<?php

namespace MovieMaps\Metadata\Location;


interface GeoCoder {

    public function searchByLocationString($location);

}