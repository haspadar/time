<?php

namespace WhatTime;

use http\Encoding\Stream\Debrotli;
use MaxMind\Db\Reader;

class Time
{
    private ?Time $countryCapital = null;
    private ?Time $stateCapital = null;
    private string $countryUrl;

    public function __construct(private array $url)
    {
        if ($this->isCountry()) {
            $this->countryCapital = WhatTime::getCountryCapital($url);
        } elseif ($this->isState()) {
            $this->stateCapital = WhatTime::getStateCapital($url);
        }

        if (!$this->isCountry()) {
            $this->countryUrl = WhatTime::getCountryUrl($this->getCountry());
        }
    }

    public function getTimezones(): array
    {
        return explode(',', $this->url['timezone']);
    }

    public function getTimezone(): string
    {
        return $this->getTimezones()[0];
    }

    public function getUrl(): string
    {
        return $this->url['url'];
    }

    public function getH1(): string
    {
        return $this->url['h1'];
    }

    public function getTitle(): string
    {
        return $this->url['title'];
    }

    public function getLatitudePercent(): string
    {
        $latitude = $this->getLatitude();
        $firstPart = intval($latitude);
        $secondPart = intval(($latitude - $firstPart) * 100);

        return $firstPart . '°' . $secondPart . '\'N';
    }

    public function getLatitude(): float
    {
        return floatval(explode(',', $this->url['coordinates'])[0]);
    }

    public function getLongitude(): string
    {
        return floatval(explode(',', $this->url['coordinates'])[1]);
    }

    public function getLongitudePercent(): string
    {
        $longitude = $this->getLongitude();
        $firstPart = intval($longitude);
        $secondPart = intval(($longitude - $firstPart) * 100);

        return $firstPart . '°' . $secondPart . '\'E';
    }

    public function getDateTime(): \DateTime
    {
        return (new \DateTime())->setTimezone(new \DateTimeZone($this->getTimezone()));
    }

    public function getUtcOffset(): int
    {
        return $this->getDateTime()->format('Z') / 3600;
    }

    public function getDayLength(): string
    {
        $diff = $this->getSunset()->getTimestamp() - $this->getSunrise()->getTimestamp();

        return floor($diff / 3600) . " hours, " . floor(($diff % 3600) / 60) . " minutes";
    }

    public function getSunrise(): \DateTime
    {
        $sunInfo = $this->getSunInfo();

        return $this->getDateTime()->setTimestamp($sunInfo['sunrise']);
    }

    public function getSunset(): \DateTime
    {
        $sunInfo = $this->getSunInfo();

        return $this->getDateTime()->setTimestamp($sunInfo['sunset']);
    }

    public function getDstStartTime(): ?\DateTime
    {
        $transitions = (new \DateTimeZone($this->getTimezone()))->getTransitions();
        $dstBefore = [];
        $dstAfter = [];
        foreach ($transitions as $transition) {
            if ($transition['isdst'] && new \DateTime($transition['time']) > new \DateTime()) {
                $dstAfter[] = new \DateTime($transition['time']);
            } elseif ($transition['isdst'] && new \DateTime($transition['time']) <= new \DateTime()) {
                $dstBefore[] = new \DateTime($transition['time']);
            }
        }

        if ($this->getDateTime()->format('I')) {
            $startTime = $dstBefore ? max($dstBefore) : null;
        } else {
            $startTime = $dstAfter ? min($dstAfter) : null;
        }

        return $startTime;
    }

    public function getDstEndTime(): ?\DateTime
    {
        if ($startTime = $this->getDstStartTime()) {
            $transitions = (new \DateTimeZone($this->getTimezone()))->getTransitions();
            foreach ($transitions as $key => $transition) {
                if (new \DateTime($transition['time']) > $startTime) {
                    return (new \DateTime($transition['time']))->modify('+1 HOUR');
                }
            }
        }

        return null;
    }

    public function getDstDescription(): string
    {
        $transitions = (new \DateTimeZone($this->getTimezone()))->getTransitions();
        $fromDateTime = (new \DateTime())->setTimezone(new \DateTimeZone($this->getTimezone()))->modify('-1 WEEK');
        $toDateTime = (new \DateTime())->setTimezone(new \DateTimeZone($this->getTimezone()))->modify('+1 WEEK');
        if (is_array($transitions) && $this->getCity()) {
            foreach ($transitions as $transition) {
                $transitionTime = (new \DateTime($transition['time']))->setTimezone(new \DateTimeZone($this->getTimezone()));
                if ($transitionTime >= $fromDateTime && $transitionTime <= $toDateTime) {
                    if ($transitionTime < new \DateTime() && $transition['isdst']) {
                        return $this->getCity()
                            . ' switched to daylight time at '
                            . $transitionTime->format('H:i')
                            . ' on ' . $transitionTime->format('d M') . '. The time was set one hour forward.';
                    } elseif ($transitionTime < new \DateTime()&& !$transition['isdst']) {
                        return $this->getCity()
                            . ' switched to standard time at '
                            . $transitionTime->modify('+1 HOUR')->format('H:i')
                            . ' on ' . $transitionTime->format('d M') . '. The time was set one hour back.';
                    } elseif ($transitionTime > new \DateTime() && $transition['isdst']) {
                        return $this->getCity()
                            . ' will be switched to daylight time at '
                            . $transitionTime->format('H:i')
                            . ' on ' . $transitionTime->format('d M') . '. The time will be set one hour forward.';
                    } elseif ($transitionTime > new \DateTime()&& !$transition['isdst']) {
                        return $this->getCity()
                            . ' will be switched to standard time at '
                            . $transitionTime->modify('+1 HOUR')->format('H:i')
                            . ' on ' . $transitionTime->format('d M') . '. The time will be set one hour back.';
                    }
                }
            }
        }

        return '';
    }

    private function getSunInfo(): array
    {
        return date_sun_info(
            $this->getDateTime()->getTimestamp(),
            $this->getLatitude(),
            $this->getLongitude()
        );
    }

    public function isCity(): bool
    {
        return $this->getCity() ? true : false;
    }

    public function isState(): bool
    {
        return $this->getState() && $this->getCountry() && !$this->getCity();
    }

    public function isCountry(): bool
    {
        return $this->getCountry() && !$this->getCity() && !$this->getState();
    }

    public function getHtmlDescription(): string
    {
        if ($this->isMainPage()) {
            return 'Check current local time in cities and countries in all time zones, adjusted for Daylight Saving Time rules. See the time difference between any cities and countries around the world.';
        }

        return 'Check exact local time and date in '
            . $this->getTitle()
            . '. Official time zone. Time difference, sunrise and sunset time. Information about Daylight Saving Time.';
    }

    public function getHtmlH1(): string
    {
        return 'Current time in ' . $this->getH1() . ' right now';
    }

    public function getHtmlTitle(): string
    {
        if ($this->isMainPage()) {
            return 'What-time.info – current exact time in your city';
        }

        return 'What time is it in ' . $this->getTitle();
    }

    public function getState(): string
    {
        return $this->url['state'];
    }

    public function getCountryUrl(): string
    {
        return $this->countryUrl;
    }

    public function getCountry(): string
    {
        return $this->url['country'];
    }

    public function getCity(): string
    {
        return $this->url['city'];
    }

    public function getFlag(): string
    {
        return (string) preg_replace_callback(
            '/./',
            static fn (array $letter) => mb_chr(ord($letter[0]) % 32 + 0x1F1E5),
            $this->url['country_code']
        );
    }

    public function getCountryCapital(): Time
    {
        return $this->countryCapital;
    }

    public function getStateCapital(): Time
    {
        return $this->stateCapital;
    }

    /**
     * @return bool
     */
    private function isMainPage(): bool
    {
        return parse_url($_SERVER['REQUEST_URI'])['path'] == '/';
    }
}