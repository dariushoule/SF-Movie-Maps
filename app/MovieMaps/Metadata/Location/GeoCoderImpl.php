<?php

namespace MovieMaps\Metadata\Location;


class GeoCoderImpl {

    private $mLocation;
    private $mProviders = null;

    /*
    * Constructor
    *
    * @param string $location The shoot location description
    */
    public function __construct($location)
    {
        $this->mLocation = $location;
        $this->mProviders = array(
            new GoogleGeocoder,
            new BingGeocoder
        );
    }

    /*
     * Retrieves a lat/lng for a location based on a description.
     * Falls back to a different provider on failures.
     *
     * @return Array lat/lng
     */
    public function getLatLng() {
        $default = array(37.7833, -122.4167);

        foreach($this->mProviders as $provider) {
            // Optimize common search for generic SF location
            if($this->mLocation == "San Francisco") {
                return $default;
            }

            $ret = $provider->searchByLocationString($this->mLocation);
            if(!empty($ret)) {
                return $ret;
            }
        }

        return $default;
    }

}