function performFrontendTests(target) {

    function resetUI() {
        $('.close').click();
        $('#map-search-container input').val('').keyup();
    }

    function resetDB(db) {
        localStorage.clear();
        $.each(function(index, name){
            db.removeCollection(name);
        });
    }

    QUnit.test("Map loaded", function( assert ) {
        assert.notEqual($('#movie-map').children().length, 0,
            'We expect the google map to have loaded');
    });

    QUnit.test("Filter results", function( assert ) {
        resetUI();
        $('#map-search-container input').val('Terminator').keyup();

        var resultTitle = $('#search-results ul li:first h3').text().trim();
        var resultId = $('#search-results ul li:first .title_id').text().trim();
        var resultLat = $('#search-results ul li:first .lat').text().trim();
        var resultLng = $('#search-results ul li:first .lng').text().trim();
        var expectedResultTitle = "Terminator - Genisys";

        assert.strictEqual(expectedResultTitle, resultTitle,
            'We expect the first filtered result for "Terminator" to be "' + expectedResultTitle + '"');

        assert.strictEqual($('#search-results ul li').length, 24,
            'We expect the count of filtered results for "Terminator" to be 24');

        assert.notEqual(resultId.length, 0,
            'We expect the filtered results to have title_id');

        assert.equal(resultLat, '37.78330000',
            'We expect the filtered results to have correct lat');

        assert.equal(resultLng, '-122.41670000',
            'We expect the filtered results to have correct lng');

        assert.strictEqual(target.getMarkers().length, 24,
            'We expect the map to have 24 visible pins');

        assert.strictEqual(
            target.getMarkers()[0].title_id, resultId,
            'We expect the map pins to store title_id');

        assert.ok(target.getMarkers()[0].position.equals(new google.maps.LatLng(resultLat, resultLng)),
            'We expect the map pins to appear at the correct lat/lng');
    });


    QUnit.test("Show and hide detail view", function( assert ) {
        var done = assert.async();
        resetUI();

        var titleId = $('#search-results ul li:first .title_id').text();
        var titleName = $('#search-results ul li:first h3').text();
        target.showDetails(titleId).done(function(){

            assert.ok($('#detail-view .loaded').is(':visible'),
                'We expect to see the details for title id: "' + titleId + '"');

            assert.strictEqual(titleName.trim(), $('#detail-view .loaded h3').text().trim(),
                'We expect view data to update for title id: "' + titleId + '"');

            $('#detail-view .close').click();
            if(target.isSmallScreen()) {

                assert.ok(
                    !$('#detail-view').is(':visible') &&
                    !$('#search-results').is(':visible') &&
                    $('#map-container').is(':visible'),
                    'We expect the close button to take us back to the map');
            } else {

                assert.ok(
                    !$('#detail-view').is(':visible') &&
                    $('#search-results').is(':visible') &&
                    $('#map-container').is(':visible'),
                    'We expect the close button to take us back to a filter list view');
            }

            done();
        });
    });

    QUnit.test("Marker pressed", function( assert ) {
        resetUI();

        target.markerSelected(target.getMarkers()[0]);

        assert.ok($('#detail-view').is(':visible'),
            'We expect a marker press to show a detail view.');

        if(target.isSmallScreen()) {
            assert.ok(!$('#map-container').is(':visible'),
                'We expect the map to hide on small screens.');
        }
    });

    QUnit.test("List item pressed", function( assert ) {
        resetUI();

        var titleLat = $('#search-results ul li:first .lat').text().trim();
        var titleLng = $('#search-results ul li:first .lng').text().trim();
        var promise = target.listItemSelected($('#search-results ul li:first'));

        var done = assert.async();
        promise.done(function(){

            assert.ok(target.getMap().getCenter().equals(new google.maps.LatLng(titleLat, titleLng)),
                'We expect a list item to take us to the correct point on the map.');

            assert.ok($('#detail-view').is(':visible'),
                'We expect a list item press to show a detail view');

            if(target.isSmallScreen()) {
                assert.ok(!$('#map-container').is(':visible'),
                    'We expect the map to hide on small screens');
            }

            done();
        });
    });

    QUnit.test("Title and location database preload", function( assert ) {
        var db = target.getDB();
        resetDB(db);

        var setupDBPromise = target.setupDB();
        var doneSetupDB = assert.async();

        setupDBPromise.done(function(){
            this.titlesPromise.done(function(){
                db = target.getDB();
                var config = db.getCollection('config');
                var titles = db.getCollection('titles');

                assert.notEqual(config.findOne({setting: 'modified'}).value, 0,
                    'We expect a non-zero modified timestamp for preloaded data.');

                var allResultSet = titles.find();
                assert.notEqual(allResultSet.length, 0,
                    'We expected non-zero number of titles preloaded in the data.');

                var terminatorResultSet = titles.find({name: 'Terminator - Genisys'});
                assert.equal(terminatorResultSet.length, 24,
                    'We expected 24 terminator locations preloaded in the data.');

                assert.strictEqual(target.setupDB(), null,
                    'We expected second DB setup to be a synchronous load from localStorage.');

                doneSetupDB();
            });
        });
    });

    QUnit.done(function(){
        $('#app-main, #main-nav').hide();
    });
};