<?php

class Actor extends Eloquent {

    protected $primaryKey = 'actor_id';
    public $timestamps = false;
    protected $table = 'actor';
    protected $hidden = array('pivot', 'actor_id');

}