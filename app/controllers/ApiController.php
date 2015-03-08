<?php

use \Title as Title;

class ApiController extends BaseController {

    /**
     * Provide a lightweight cache of titles and locations for the application to generate pins and search on.
     *
     * @return Json array of title id/name to location lat/lng
     */
	public function titlesAndLocations() {

        $titles = DB::table('title')
            ->leftJoin('title_location', 'title_location.title_id', '=', 'title.title_id')
            ->leftJoin('location', 'title_location.location_id', '=', 'location.location_id')
            ->get(array('title.title_id', 'title.name', 'location.description', 'location.lat', 'location.lng'));

        return Response::json($titles);
    }

    /**
     * Provide a full title object for an id including relationships.
     *
     * @param Number $titleId
     * @return Json full title object
     */
    public function title($titleId) {

        $title = Title::where('title_id', $titleId)->with(array('actors', 'locations'))->get();

        return Response::json(array('title' => $title));
    }

    /**
     * Provide the last modification date of title data.
     *
     * @return Json object containing modified as timestamp value
     */
    public function lastModified() {

        $title = Title::orderBy('updated_at', 'DESC')->first(array('updated_at'));
        $updatedAt = !empty($title->updated_at) ? strtotime($title->updated_at) : 0;

        return Response::json(array('modified' => $updatedAt));
    }
}
