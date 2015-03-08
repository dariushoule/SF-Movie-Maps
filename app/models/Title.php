<?php

class Title extends Eloquent {

    protected $primaryKey = 'title_id';
    protected $table = 'title';
    protected $hidden = array('title_id');

    public function actors() {
        return $this->belongsToMany('Actor', 'title_actor');
    }

    public function locations() {
        return $this->belongsToMany('Location', 'title_location');
    }

}