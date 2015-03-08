<?php

namespace MovieMaps\ExternalRequest;


class Json {

    /*
     * Retrieves a JSON network resource and decodes it.
     *
     * @param string $url The location of the data
     * @return Array decoded object
     */
    public static function get($url) {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER      => 1,
            CURLOPT_URL                 => $url,
            CURLOPT_SSL_VERIFYPEER      => false,
            CURLOPT_FAILONERROR         => true
        ));

        $request = curl_exec($curl);
        if (empty($request)) {
            return false;
        }
        curl_close($curl);
        return json_decode($request, true);
    }

}