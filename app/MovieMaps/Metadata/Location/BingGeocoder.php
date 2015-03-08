<?php

namespace MovieMaps\Metadata\Location;

use MovieMaps\ExternalRequest\Json;

class BingGeocoder implements GeoCoder {

    /*
     * Retrieves a lat/lng for a location based on a description using Bing's geocoder.
     *
     * @return false|Array lat/lng or false on failure
     */
    public function searchByLocationString($location)
    {
        $location = urlencode($location);
        $apiKey = urlencode(\Config::get('maps.bing.apikey'));
        $params = "q=$location" .
            "&key=$apiKey" .
            "&o=json" .
            "&maxRes=1";

        $response = Json::get('http://dev.virtualearth.net/REST/v1/Locations?' . $params);
        if($response['statusDescription'] !== "OK" || empty($response['resourceSets'][0]['resources'])) {
            return false;
        } else {
            $loc = $response['resourceSets'][0]['resources'][0]['point']['coordinates'];
            return array($loc[0], $loc[1]);
        }
    }
}