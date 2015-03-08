(function() {

    var self = {}, loaded = false;
    var map, infowindow = new google.maps.InfoWindow({ content: '' });
    var db, config, titles;
    var viewModel;
    var markers = [];

    /*
    * Knockout viewmodel for list and detail sidebar views.
    * @constructor
    */
    function IndexViewModel() {
        this.sidebarItems = ko.observableArray();
        this.details = ko.observable({});

        this.yearFormatted = ko.computed(function(){
            var details = this.details();
            if(typeof details === "undefined") {
                return "";
            }

            return (typeof details.year === "string") ? details.year.substring(0, 4) : "";
        }, this);
    };

    /* Getters */

    self.getMarkers = function() {
        return markers;
    };

    self.getMap = function() {
        return map;
    };

    self.getDB = function() {
        return db;
    };

    /*
    * Checking this element tells us if we are using a small form factor device
    * @returns {Boolean}
    */

    self.isSmallScreen = function() {
        return !$('#phone-factor-hidden').is(":visible");
    };

    /*
     * Filters map pins and list view down to the existing text in the search box with left and right wildcard
     */
    self.filterResults = function()  {
        var search = $('#map-search-container input').val();
        var results = titles.find({'name': {'$regex' : new RegExp(search, "i") }});
        viewModel.sidebarItems(results);

        if($('#detail-view').is(":visible")) {
            $('#search-results').show();
            $('#detail-view').hide();
        }

        self.refreshMap();
    };

    /*
     * Shows either the fullscreen or sidebar detail view for a title depending on column layout.
     * A loading spinner will be visible in place of the details until the promise is resolved.
     *
     * @param {Number} title_id of the movie
     * @param {google.maps.LatLng} position of the pin to pan to (will also open info window on desktop view)
     * @returns {Deferred} promise for the load of the title information
     */
    self.showDetails = function(title_id, position) {
        $('#search-results').hide();
        $('#detail-view').show();
        $('#detail-view .loading').show();
        $('#detail-view .loaded').hide();

        if(self.isSmallScreen()) {
            $('#map-container').hide();
        }

        if(!self.isSmallScreen()) {
            $.each(markers, function (index, marker) {
                if (marker.title_id == title_id && marker.position.equals(position)) {
                    infowindow.setContent('<strong>' + marker.name + '</strong>');
                    infowindow.open(map, marker);
                    return false;
                }
            });
        }

        return $.getJSON("/title/" + title_id).done(function(data) {
            viewModel.details(data.title[0]);
            $('#detail-view .loading').hide();
            $('#detail-view .loaded').show();

        }).fail(function(){
            console.error("Unable to retrieve title data for: " + title_id);
        });
    };

    /*
     * Callback for a marker being selected on the map.
     * Hides the map to make room for full-screen details on small form factors.
     *
     * @param {google.maps.Marker} marker the marker selected
     * @returns {Deferred} promise for the load of the title information
     */
    self.markerSelected = function(marker) {
        if(self.isSmallScreen()) {
            $('#map-container').hide();
        }

        return self.showDetails(marker.title_id, marker.position);
    };

    /*
     * Clears pins and events from a map and repopulates with current filtered data.
     */
    self.refreshMap = function() {
        $.each(markers, function(index, marker){
            marker.setMap(null);
            marker = null;
        });

        google.maps.event.clearListeners(map, 'click');

        markers = [];
        $.each(viewModel.sidebarItems(), function(index, title){
            var marker = new google.maps.Marker({
                position: new google.maps.LatLng(title.lat, title.lng),
                map: map,
                title_id: title.title_id,
                name: title.name
            });

            markers.push(marker);

            google.maps.event.addListener(marker, 'click', function() {
                self.markerSelected(this);
            });
        });

        loaded = true;
    };

    /*
     * Slides the sidebar in post-initialization (only) on desktop form factors.
     */
    self.showSidebar = function() {
        viewModel.sidebarItems(titles.data);

        if (!self.isSmallScreen()) {
            $('#map-container').animate({width: "75%"}, 700, function () {
                $(this).css('width', '');
            });

            $('#search-results').show().animate({width: "25%"}, 700, function () {
                $(this).css('width', '');
            });
        }

        self.refreshMap();
    };

    /*
     * Retrieves a cache of bare title and location data for displaying pins on the map and filtering.
     *
     * @returns {Deferred} promise for the load of the information
     */
    self.getTitlesAndLocations = function() {
        return $.getJSON("/titles-and-locations/").done(function(data) {
            titles.removeDataOnly();

            $.each(data, function(index, title){
                titles.insert(title);
            });

            db.save();
        }).fail(function() {
            try {
                if (db.getCollection('titles').data.length > 0) {
                    console.warn("Unable to refresh preloaded title data, falling back on old data.");
                    return;
                }
            } catch(e) {}

            throw new Exception("Fatal: Unable to retrieve preload data for application");
        });
    };

    /*
     * Callback for a list item being selected on the map.
     *
     * @param {DOMElement} li selected
     * @returns {Deferred} promise hash for the load of the title information as well as the completion of the map panning.
     */
    self.listItemSelected = function(item) {
        var latLng = new google.maps.LatLng($(item).find('.lat').html(), $(item).find('.lng').html());
        map.panTo(latLng);
        return $.when(self.showDetails($(item).find('.title_id').html(), latLng), self.GMSIdlePromise());
    };

    /*
     * Checks the servers last modified date for title/location cache information and refreshes when needed.
     * When application is up to date title/location data previously pulled from localStorage is used.
     *
     * @returns {Deferred} promise for the load of the modified date
     */
    self.preloadData = function() {

        config = db.getCollection('config');
        titles = db.getCollection('titles');

        if(config === null) {
            titles = db.addCollection('titles');
            config = db.addCollection('config');
            config.insert({setting: 'modified', value: 0});
        }

        var modifiedDoc = config.findOne({setting: 'modified'});
        return $.getJSON("/last-modified/").done(function(data) {
            if (modifiedDoc.value !== data.modified) {
                modifiedDoc.value = data.modified;
                config.update(modifiedDoc);

                this.titlesPromise = self.getTitlesAndLocations().always(self.showSidebar);
            } else {
                self.showSidebar();
            }
        }).fail(function() {
            console.error("Unable to retrieve last modified date for preload data, forcing reload.");
            this.titlesPromise = self.getTitlesAndLocations().always(self.showSidebar);
        });
    };

    /*
     * Generates a promise that can be used to tell when a google map has finished an operation and is now idle.
     *
     * @returns {Deferred} promise for the 'idle' map event
     */
    self.GMSIdlePromise = function() {
        var promise = $.Deferred();

        google.maps.event.addListener(map, 'idle', function() {
            google.maps.event.clearListeners(map, 'idle');
            promise.resolve();
        });

        return promise;
    };

    /*
     * Creates a new map element and performs initial tile loading.
     *
     * @returns {Deferred} promise for completion of map loading
     */
    self.loadGMS = function() {
        map = new google.maps.Map(document.getElementById('movie-map'), {
            center: {lat: 37.7833, lng: -122.4167},
            zoom: 14
        });

        return self.GMSIdlePromise();
    };

    /*
     * Creates a new document store to cache bare title/location data.
     * Pulls from localStorage when possible to avoid server reloads when data is unchanged.
     *
     * @returns {null|Deferred} promise for completion of db load or null on synchronous load
     */
    self.setupDB = function() {
        db = new loki('frontendCache');
        try {
            // Pull from localstorage adapter is synchronous, so no DB load promise to return here.
            db.loadDatabase(null, self.preloadData);
            return null;
        } catch(e) {
            // No saved data, just do a fresh load
            return self.preloadData();
        }
    };

    /*
     * One-off initialization of the application, fires DB/Map loads, initalizes knockout viewmodel
     * and sets up event handling for controls.
     *
     * @returns {Deferred} promise hash for completion one off load events
     */
    self.init = function() {
        // Attempt to hide address bars on phones
        setTimeout(function(){
            window.scrollTo(0, 1);
        }, 0);

        // Make this a full height application
        $(window).resize(function() {
            $('#search-results, #movie-map, #detail-view').height($(window).height() - 50);

            // Handle hiding of sidebar on small form factors
            if (loaded) {
                if (self.isSmallScreen()) {
                    if($('#map-container').is(':visible')) {
                        $('#search-results, #detail-view').hide();
                    }
                } else {
                    $('#map-container').show();
                    if(!$('#detail-view').is(":visible")) {
                        $('#search-results').show();
                    }
                }
            }

            // Center the logo image on small screens
            if (self.isSmallScreen()) {
                $('#main-nav img').removeClass('pull-left');
            } else {
                $('#main-nav img').addClass('pull-left');
            }
        }).resize();

        $(document).on('click', '#search-results li', function(){
            self.listItemSelected(this);
        });

        $('.close').click(function(){
            if($('#map-container').is(':visible')) {
                $('#search-results').show();
            }

            $('#map-container').show();
            $('#detail-view').hide();
        });

        $('#map-search-container input').keyup(self.filterResults);
        ko.applyBindings((viewModel = new IndexViewModel()));

        var dbSetupPromise = self.setupDB();
        return dbSetupPromise === null ? $.when(self.loadGMS()) : $.when(self.loadGMS(), dbSetupPromise);
    };

    // Bootstrap the application on window load.
    google.maps.event.addDomListener(window, 'load', function(){

        var promise = self.init();
        if(window.location.search.indexOf('testing=true') != -1) {
            promise.done(function(){
                performFrontendTests(self);
            });
        }
    });
})();