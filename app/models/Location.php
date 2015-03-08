<?php

class Location extends Eloquent {

    protected $primaryKey = 'location_id';
    public $timestamps = false;
    protected $table = 'location';
    protected $hidden = array('pivot', 'location_id');

}