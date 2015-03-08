<?php
namespace MovieMaps\DataIngestion;

use MovieMaps\Metadata\Image\ImageSearchImpl;
use MovieMaps\Metadata\Image\ImageCache;
use MovieMaps\Metadata\Location\GeoCoderImpl;
use \Title as Title;

/*
 * This class is responsible for taking title data from the DataSF datasource and loading it into the system.
 *
 * https://data.sfgov.org/Arts-Culture-and-Recreation-/Film-Locations-in-San-Francisco/yitu-d5am
 *
 */


class DataSFTitleLoader extends TitleLoader {

    const COLLAPSE_ERROR = "Problem when collapsing entries, data could not be understood.";

    /**
     * Constructor
     *
     * @param String $uri A resource to retrieve the data set from.
     */
    public function __construct($uri) {
        parent::__construct($uri);
    }

    /**
     * Collapse redundant entries down to a single entity with nested locations and actors as opposed to flat.
     * Normalizes any issues with missing keys.
     *
     * @param Array $entries
     * @return Array the collapsed data set
     */
    private function collapseEntries($entries) {
        $collapsed = array();

        if(empty($entries) || !is_array($entries)) {
            return null;
        }

        foreach ($entries as $entry) {

            if (empty($collapsed[$entry['title']])) {
                $entry['actors'] = array();
                foreach ($entry as $key => $val) {
                    if (starts_with($key, 'actor_')) {
                        $entry['actors'][] = $val;
                        unset($entry[$key]);
                    }
                }

                // If location information isn't present just use city center
                $entry['locations'] = array(!empty($entry['locations']) ? $entry['locations'] : "San Francisco");
                $collapsed[$entry['title']] = $entry;
            } else {
                $collapsed[$entry['title']]['locations'][] = $entry['locations'];
            }
        }

        return array_values($collapsed);
    }

    /*
     * Prepares retrieved data for ingestion into database, only modifying or inserting rows where needed.
     * Also performs geolocation lookups on the locations and thumbnail image generation.
     *
     * @return bool success
     */
    public function prepareLoad() {
        $this->mUpdates = array();

        $entries = $this->collapseEntries($this->mData);
        if($entries === null) {
            $this->mError = self::COLLAPSE_ERROR;
            return false;
        }

        foreach ($entries as $entry) {
            $update = array();
            $update['title'] = Title::where('name', $entry['title'])->first();
            $checksum = $this->getEntryChecksum($entry);

            if(empty($update['title'])) {
                $update['title'] = new Title;
            }

            if($update['title']->checksum != $checksum) {
                $update['title']->name = $entry['title'];
                $update['title']->distributor = !empty($entry['distributor']) ? $entry['distributor'] : 'Unknown';
                $update['title']->producer = !empty($entry['production_company']) ? $entry['production_company'] : 'Unknown';
                $update['title']->writer = !empty($entry['writer']) ? $entry['writer'] : 'Unknown';
                $update['title']->director = !empty($entry['director']) ? $entry['director'] : 'Unknown';
                $update['title']->fun_facts = !empty($entry['fun_facts']) ? $entry['fun_facts'] : 'Unknown';
                $update['title']->year = $entry['release_year'] . '-00-00';
                $update['title']->checksum = $checksum;
                $update['actors'] = $entry['actors'];

                foreach($entry['locations'] as $desc) {
                    $geocoder = new GeoCoderImpl($desc);
                    $location = array('description' => $desc);
                    list($location['lat'], $location['lng']) = $geocoder->getLatLng();
                    $update['locations'][] = $location;
                }

                $imageSearch = new ImageSearchImpl($entry['title']);
                $remote_address = $imageSearch->getPoster();
                $update['title']->image_url = ImageCache::store($remote_address);

                $this->mUpdates[] = $update;
            }
        }

        return true;
    }

}