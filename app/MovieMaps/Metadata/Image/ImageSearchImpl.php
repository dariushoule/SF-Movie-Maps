<?php

namespace MovieMaps\Metadata\Image;



class ImageSearchImpl {

    private $mTitle;
    private $mProviders = null;

    /*
     * Constructor
     *
     * @param string $title The movie title
     */
    public function __construct($title) {
        $this->mTitle = $title;
        $this->mProviders = array(
            new OMDBImageSearch,
            new IMDBImageSearch
        );
    }

    /*
     * Retrieves a poster image from an available search provider based on title.
     * Falls back to a different provider on failures.
     *
     * @return string the filename of the written file or an empty string on failure.
     */
    public function getPoster() {
        foreach($this->mProviders as $provider) {
            $image = $provider->searchByTitle($this->mTitle);
            if(!empty($image)) {
                return $image;
            }
        }

        return '';
    }
}