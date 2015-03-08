<?php

namespace MovieMaps\Metadata\Location;

use MovieMaps\ExternalRequest\Json;


class GoogleGeocoder implements GeoCoder {

    /*
     * Retrieves a lat/lng for a location based on a description using Google's geocoder.
     *
     * @return false|Array lat/lng or false on failure
     */
    public function searchByLocationString($location)
    {
        // Geocode address with SF bias
        $location = urlencode($location);
        $apiKey = urlencode(\Config::get('maps.google.apikey'));
        $params = "address=$location" .
            "&key=$apiKey" .
            "&bounds=37.70339999999999,-122.527|37.812,-122.3482";

        $response = Json::get('https://maps.googleapis.com/maps/api/geocode/json?' . $params);
        if($response['status'] !== "OK") {
            return false;
        } else {
            $loc = $response['results'][0]['geometry']['location'];
            return array($loc['lat'], $loc['lng']);
        }
    }
}