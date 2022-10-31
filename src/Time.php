<?php

namespace Time;

use MaxMind\Db\Reader;

class Time
{
    private $url;
    /**
     * @var mixed
     */
    private $timezone = 'Europe/Kiev';
    /**
     * @var mixed
     */
    private $description;
    /**
     * @var mixed
     */
    private $latitude = '50.450001';
    /**
     * @var mixed
     */
    private $longitude = '30.523333';
    /**
     * @var mixed|string
     */
    protected $city;
    /**
     * @var mixed|string
     */
    private $country;

    public function __construct(string $locationUrl, string $ip)
    {
        if ($locationUrl && $this->url = \DB::queryFirstRow('SELECT * FROM urls WHERE url=%s', $locationUrl)) {
            $this->timezone = $this->url['timezone'];
            $this->latitude = floatval(explode(',', $this->url['coordinates'])[0]);
            $this->longitude = floatval(explode(',', $this->url['coordinates'])[1]);
            $this->description = $this->url['title'];
            $this->city = $this->url['city'];
            $this->country = $this->url['country'];
        } else {
            $reader = new Reader('../GeoLite2-City.mmdb');
            if (filter_var($ip, FILTER_VALIDATE_IP) && $ip != '::1') {
                $city = $reader->get($ip);
                $names = $city['city']['names'];
                $this->timezone = $city['location']['time_zone'];
                $cityName = $names[0] ?? $names['en'] ?? explode('/', $this->timezone)[1];
                $this->city = $cityName;
                $country = $city['country']['names']['en'] ?? $city['country']['names'][0] ?? '';
                $this->country = $country;
                $this->description = $cityName . ', ' . $country;
                $this->latitude = $city['location']['latitude'];
                $this->longitude = $city['location']['latitude'];
            }
        }
    }

    /**
     * @return mixed|string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return mixed|string
     */
    public function getTimezone()
    {
        return $this->timezone;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return mixed|string
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * @return mixed|string
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    public function getDateTime(): \DateTime
    {
        return (new \DateTime())->setTimezone(new \DateTimeZone($this->timezone));
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

    private function getSunInfo()
    {
        return date_sun_info(
            $this->getDateTime()->getTimestamp(),
            $this->getLatitude(),
            $this->getLongitude()
        );
    }

    /**
     * @return mixed|string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @return mixed|string
     */
    public function getCity()
    {
        return $this->city;
    }
}