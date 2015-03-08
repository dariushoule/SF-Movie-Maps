<?php

Route::get('/', 'HomeController@appMain');
Route::get('/titles-and-locations', 'ApiController@titlesAndLocations');
Route::get('/title/{title_id}', 'ApiController@title');
Route::get('/last-modified', 'ApiController@lastModified');
