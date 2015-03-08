<?php
namespace MovieMaps\Metadata\Image;

use MovieMaps\ExternalRequest\Json;

class OMDBImageSearch implements ImageSearch {

    /*
     * Retrieves a poster image from the OMDB api based on title
     *
     * @param string $title The movie title
     * @return string a hosted URL (usually non-hotlinkable)
     */
    public function searchByTitle($title)
    {
        $title = urlencode($title);
        $response = Json::get("http://www.omdbapi.com/?t=$title");
        return !empty($response['Poster']) ? $response['Poster'] : false;
    }
}