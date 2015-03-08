<?php

namespace MovieMaps\DataIngestion;

use \Actor as Actor;
use \Location as Location;
use \Illuminate\Support\Facades\DB as DB;

/*
 * This base class is responsible for providing the means to take title data from a datasource and load it into the system.
 *
 */

abstract class TitleLoader {

    const RETRIEVAL_ERROR = "File or network resource could not be reached.";

    private $mDataSource;
    protected $mError;
    protected $mData;
    protected $mUpdates;

    /*
     * Creates a new TitleLoader
     *
     * @param string $uri The location of the data, file or URL
     */
    public function __construct($uri) {
        $this->mDataSource = $uri;
        $this->mError = "";
    }

    /*
     * Attempts to retrieve and decode data source.
     *
     * @return boolean success
     */
    public function retrieveData() {
        $this->mData = json_decode(file_get_contents($this->mDataSource), true);
        if(empty($this->mData)){
            $this->mError = self::RETRIEVAL_ERROR;
            return false;
        }

        return true;
    }

    /*
     * Prepares retrieved data for ingestion into database.
     *
     * @return boolean success
     */
    abstract public function prepareLoad();

    /*
     * Returns the last error encountered.
     *
     * @return boolean success
     */
    public function getError() {
        return $this->mError;
    }

    /*
     * Returns fast checksum for an entry.
     *
     * @return boolean success
     */
    protected function getEntryChecksum($obj) {
        $strRepresentation = print_r($obj, true);
        return crc32($strRepresentation);
    }

    /*
     * Retrieves an actor from the database. If they don't already exist parse the name and store them.
     *
     * @param string $first
     * @param string $last
     * @return mixed false on failure Actor on success
     */
    private function retrieveActor($first, $last = null)
    {
        // If last isn't provided than this name may need processing
        if ($last == null) {
            $nameParts = explode(" ", $first);

            if (count($nameParts) == 1) {
                // Only a first name
                $first = $nameParts[0];
                $last = '';
            } else if (count($nameParts) == 2) {
                // Name separated by space
                $first = $nameParts[0];
                $last = $nameParts[1];
            } else if (count($nameParts) == 3) {
                // Ignore the middle name
                $first = $nameParts[0];
                $last = $nameParts[2];
            } else {
                // Can't understand name format
                $this->mError = "Error processing malformed actor name: $first";
                return false;
            }
        }

        $actor = Actor::where(array('first' => $first, 'last' => $last))->first();
        if(empty($actor)) {
            $actor = new Actor;
            $actor->first = $first;
            $actor->last = $last;
            $actor->save();
        }

        return $actor;
    }

    /*
     * Retrieves a location from the database. Creates a new entry if not found.
     *
     * @param string $location
     *
     * @return Location
     */
    private function retrieveLocation($location)
    {
        $newLocation = Location::where('description', $location['description'])->first();
        if(empty($newLocation)) {
            $newLocation = new Location;
            $newLocation->description = $location['description'];
            $newLocation->lat = $location['lat'];
            $newLocation->lng = $location['lng'];
            $newLocation->save();
        }

        return $newLocation;
    }

    /*
     * Updates and inserts title/actor information in the database where needed.
     *
     * @return boolean
     */
    public function performLoad() {

        // Update title information that has changed
        foreach($this->mUpdates as $update) {

            foreach($update['actors'] as $actorName) {

                if(($actor = $this->retrieveActor($actorName))) {
                    $update['actor_ids'][] = $actor->actor_id;
                } else {
                    return false;
                }
            }

            foreach($update['locations'] as $location) {

                if(($location = $this->retrieveLocation($location))) {
                    $update['location_ids'][] = $location->location_id;
                } else {
                    return false;
                }
            }

            // Save and sync many to many relationships
            $update['title']->save();

            if(!empty($update['actor_ids'])) {
                $update['title']->actors()->sync($update['actor_ids']);
            }

            if(!empty($update['location_ids'])) {
                $update['title']->locations()->sync($update['location_ids']);
            }
        }

        // Remove items orphaned by the update
        DB::table('actor')
            ->leftJoin('title_actor', 'title_actor.actor_id', '=', 'actor.actor_id')
            ->whereNull('title_actor.actor_id')
            ->delete();

        DB::table('location')
            ->leftJoin('title_location', 'title_location.location_id', '=', 'location.location_id')
            ->whereNull('title_location.location_id')
            ->delete();

        return true;
    }
}