<?php
namespace MovieMaps\Metadata\Image;

use MovieMaps\ExternalRequest\Json;

class IMDBImageSearch implements ImageSearch {

    /*
     * Retrieves a poster image from the IMDB api based on title
     *
     * @param string $title The movie title
     * @return string an IMDB hosted URL (non-hotlinkable)
     */
    public function searchByTitle($title)
    {
        $title = urlencode($title);
        $response = Json::get("http://www.imdbapi.com/?t=$title");
        return !empty($response['Poster']) ? $response['Poster'] : false;
    }
}