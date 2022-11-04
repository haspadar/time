<?php

namespace WhatTime;

use http\Encoding\Stream\Debrotli;
use MaxMind\Db\Reader;

class Time
{
    public function __construct(private array $url)
    {
    }

    public function getTimezone(): string
    {
        return explode(',', $this->url['timezone'])[0];
    }

    public function getUrl(): string
    {
        return $this->url['url'];
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
}