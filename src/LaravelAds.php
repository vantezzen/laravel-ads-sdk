<?php namespace LaravelAds;

use LaravelAds\Services\GoogleAds\Service AS GoogleAdsService;
use LaravelAds\Services\BingAds\Service AS BingAdsService;

class LaravelAds
{
    /**
     * service()
     *
     * @return static method
     */
    public static function service($service) {
        if ($service == 'GoogleAds') return static::googleAds();
        if ($service == 'BingAds') return static::bingAds();
        if ($service == 'FacebookAds') return static::facebookAds();
    }

    /**
     * googleAds()
     * Google Ads
     *
     */
    public static function googleAds() {
        return (new GoogleAdsService());
    }

    /**
     * bingAds()
     * Bind Ads
     *
     */
    public static function bingAds() {
        return (new BingAdsService());
    }

    /**
     * facebookAds()
     * Facebook Ads
     *
     */
    public static function facebookAds() {
        return false;
    }

}
