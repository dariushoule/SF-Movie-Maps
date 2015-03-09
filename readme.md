## San Francisco Movie Map

This application is based on the requirements detailed on Uber's [SF Movies coding challenge](https://github.com/uber/coding-challenge-tools/blob/master/coding_challenge.md#sf-movies).

At a high level, it is able to ingest the data from [this source](https://data.sfgov.org/resource/yitu-d5am.json) to display the locations of popular movies filmed in San Francisco. Live demo can be found here: [http://sfmap.dbot2000.com/](http://sfmap.dbot2000.com/)


### How it works: Backend

Data is loaded from the source using a CLI command. Within the applications folder run ```"php artisan load:titles"```. This can be automated using a cron job, to keep the application in sync with the datasource on a schedule.

After processing, information is stored in your choice of a PostgreSQL or MySQL database.

![Image](erd.png?raw=true)


#### Ingestion process

1. Data is retrieved from the source and validated as JSON.
2. Data is flattened down to a single entry with nested locations and actors. This is necessary as the data source is not normalized. 
3. A checksum is calculated for each entry and preprocessing occurs
	- If the title exists in the database but its checksum is different, updates are generated for the entry. 
	- If the title does not exist in the database, inserts are generated.
		- Missing locations and malformatted names are resolved.
		- Poster images are retrieved based on the movie's title.
		- Location descriptions are geolocated.
4. The movies and relationships are inserted to the database.
	- Unused title and location entries are removed.
	
	
#### API

The backend provides a dead-simple three-endpoint API.

```GET /titles-and-locations/```
	- Returns JSON array payload containing the bare information needed to display pins and populate the sidebar.
	
*Example Response*

```
[{
	"title_id":"2000",
	"name":"Movie name",
	"description":"Description of film location",
	"lat":"37.79926270",
	"lng":"-122.39767320"
},...
```

```GET /title/{title_id}```
	- Returns JSON object containing the information needed to display complete details for a movie. 
	
*Example Response*

```
{
    "title": [{
        "name": "High Crimes",
        "producer": "Twentieth Century Fox Film Corporation",
        "distributor": "Twentieth Century Fox Film Corporation",
        "writer": "Mel Brooks",
        "director": "Mel Brooks",
        "image_url": "posters\/img_poster_54f89f28cb8d6.png",
        "fun_facts": "The Bank of America Building was the tallest building on the West Coast from 1969-1972, when it was surpassed by the TransAmerica Pyramid. Today, the Bank of America building is the 5th tallest building on the West Coast.",
        "year": "1977-00-00",
        "checksum": "203390316",
        "created_at": "2015-03-05 18:29:19",
        "updated_at": "2015-03-05 18:29:19",
        "actors": [{
            "first": "Mel",
            "last": "Brooks"
        }, {
            "first": "Madeline",
            "last": "Kahn"
        }],
        "locations": [{
            "description": "Bank of America Building (555 California Street)",
            "lat": "37.80310000",
            "lng": "-122.27170000"
        }, {
            "description": "391 Pennsylvania Avnue at 19th Street",
            "lat": "37.76148150",
            "lng": "-122.39348120"
        }, ...]
    }]
}
```

```GET /last-modified/```
	- Returns JSON object containing a last-modified timestamp of most recent data loaded. This allows API consumers to know when cached data should be purged.
	
*Example Response*

```
{"modified":1425580161}
```

##### Highlights

- The poster finding and geocoding functions of the application have fallbacks.
	- Google geocoding is preferred but falls back to Bing on failure or not-found.
	- OMDB's movie poster search is preferred over IMDB's API unless there is a failure.
- Posters are downloaded from the source and cached on disk to avoid hotlinking issues.
- Application CSS and Javascript files are minified and combined.


##### Tests

Tests can be run with PHPUnit in the main application directory. They cover the basic load and processing of data.


### How it works: Frontend

This is a responsive single page frontend with three views. A map and list view show filming locations that when selected display a detail pane. On small screens this detail pane takes the whole view and hides the map. On desktop form factors it is part of the sidebar.

![UI](main.jpg?raw=true)


##### Highlights

- Initial data needed to load pins and populate the sidebar is precached to the browser's localStorage.
	- This allows for fast loads after the initial visit.
	- This means the most expensive query needed to service a client won't be called every visit.
		- The localStorage cache is busted when the ```last-modified``` endpoint value changes.
- A subset of additional tests are available for testing on small form factor devices. Simply load the test on a small device or sized down browser.


##### Tests

Tests can be run with QUnit by appending ```?testing=true``` to the index URL. They cover the basic UI functionality and application library. 


### Locations of notable files

The files worth taking a look at in the project reside in the following locations:

```public/js/(app|test).js``` The majority of the frontend functionality resides here

```app/MovieMaps/*``` The majority of the backend functionality resides here

```app/test/*``` Backend tests

```public/css/app.css``` Main stylesheet

```app/controllers/*``` Homepage and API controllers

```app/views/*``` Homepage markup

```Vagrantfile``` Vagrant configuration

```provisioner.sh``` Shell provisioner for local environment setup


### Technology Stack

*Backend*

- ```PHP/Laravel framework```
	- ```Laravel ORM``` and ```Blade``` Templating engine
	- ```PostgreSQL``` or ```MySQL``` Database engine
	
*Frontend*

- ```Bootstrap/jQuery``` Responsive layout and utility library
- ```qUnit``` Functionality testing
- ```LokiJS``` In-memory movie database, filtering, localStorage persistence
- ```Knockout``` UI bindings

*API's Used*

- ```Socrata Open Data API (SODA)```
- ```Google/Bing Geocoder```
- ```(O/I)MDB title metadata search```


### Local environment setup

Setting up the application locally is easy and self-contained using vagrant -- dependent on a VirtualBox provider.

```
cd /<project directory>/
vagrant up
```

The application will be set up with a shell provisioner and available on **10.0.0.222**


### Before live deployment

1. Set your Bing and Google maps API keys in ```app/config/maps.php```
2. Fill out your database connection info and set the default in ```app/config/database.php```
3. Generate an application encryption key using ```php artisan key:generate```
4. Set your application URL in ```app/config/app.php```
5. Ensure dependencies are up to date with ```composer update```


### TODO

If I had more time to take on the project, here are some of the first things I'd work on.

**Backend Tests**

The backend tests for the application don't cover nearly as many edge cases as I'd like. Beyond the obvious malformed response I'd really like to cover things like: 

- Malformed attributes
- Geocoder/Image search fallbacks
- Network and filesystem failures
- Malformed queries to the API
	
**Frontend Tests**

Much like the backed the frontend tests are fairly "happy-path". Beyond the obvious usability of the application they could use testing around: 

- Testing of promise failure conditions (slow/dead network and map failures)
- Viewport resize functionality
- Malformed filter values

**Frontend improvements on small devices**

The performance and usability on mobile devices could be significantly improved with some refactoring. Some initial things to look at:

- Don't apply the knockout bindings for the list-view sidebar on small form factors.
- Eliminate need to iterate pins for lat/lng.
- Left and right side wildcard search might not be the best choice on phones.
- The navigation bar is larger than it needs to be on phones.
- The address bar can't be scrolled up due to the way the application height is determined.

**Misc**

- The ability to build a tour by chaining together pins would be a great feature. As would directions from current location. 
- Porting the application styles from CSS to LESS would make it more readable.
- The geocoder accuracy is much worse when the location description spans an entire street or holds addresses in parenthesis. By preprocessing some of these we can make them more understandable to the geocoder.
