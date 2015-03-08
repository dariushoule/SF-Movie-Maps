<?php

namespace MovieMaps\Metadata\Image;


class ImageCache {

    /*
     * Retrieves a poster image from a network resource and caches it in the public folder.
     *
     * @param string $url The network location of the existing resource
     * @return string the filename of the written file or an empty string on failure.
     */
    public static function store($url) {
        if(empty($url) || strpos($url, "http") === false) {
            return "";
        }

        try {
            $fileContent = file_get_contents($url);
        } catch(Exception $e) {
            return "";
        }

        $filename = "posters/" . uniqid("img_poster_") . ".png";
        $fileDestination = __DIR__ . "/../../../../public/$filename";

        file_put_contents($fileDestination, $fileContent);
        return $filename;
    }
}