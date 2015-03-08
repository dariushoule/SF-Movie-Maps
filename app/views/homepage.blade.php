<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>SF Movie Map</title>
    <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0' name='viewport' />

    <link rel="stylesheet" type="text/css" href="{{ Minify::combine($cssFiles, '.css') }}">

    <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBML-XGITAFX2VEdZKua4Uo5SeoxEKpEzg"></script>
    <script type="text/javascript" src="{{ Minify::combine($jsFiles, '.js') }}" type="text/javascript"></script>
</head>
<body>

    <nav id="main-nav" class="navbar navbar-default navbar-fixed-top">
        <div class="container-fluid">

            <div class="row text-center">
                <img src="{{ URL::asset('img/logo.png') }}" class="pull-left">
                <form id="map-search-container" class="col-md-3 navbar-right" role="search" onsubmit="return false;">
                    <input type="text" class="form-control" placeholder="Search Movie Names...">
                </form>
            </div>

        </div>
    </nav>

    <div id="app-main" class="container-fluid">
        <div class="row no-pad">
            <div id="map-container" class="col-sm-9">
                <div id="movie-map"></div>
            </div>
            <div id="search-results" class="col-sm-3" style="display: none;">
                <ul data-bind="foreach: sidebarItems">
                    <li>
                        <h3 data-bind="text: name"></h3>
                        <p data-bind="text: description"></p>
                        <span class="hidden title_id" data-bind="text: title_id"></span>
                        <span class="hidden lat" data-bind="text: lat"></span>
                        <span class="hidden lng" data-bind="text: lng"></span>
                        <div class="pull-right right-chevron">
                            <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
                        </div>
                    </li>
                </ul>
            </div>
            <div id="detail-view" class="col-sm-3" style="display: none;">
                <div class="loading">
                    <img src="{{URL::asset('img/loader.gif')}}">
                </div>
                <div class="loaded row">
                    <div class="close">
                        <span class="glyphicon glyphicon glyphicon-circle-arrow-left" aria-hidden="true"></span>
                    </div>
                    <h3 class="col-xs-12">
                        <span data-bind="text: details().name"></span>
                    </h3>
                    <div class="col-xs-12">
                        <div class="img-container">
                            <img data-bind="attr: { src: details().image_url }">
                        </div>
                    </div>
                    <p class="col-xs-12">
                        <strong>Producer:</strong>
                        <span data-bind="text: details().producer"></span>
                    </p>
                    <p class="col-xs-12">
                        <strong>Distributor:</strong>
                        <span data-bind="text: details().distributor"></span>
                    </p>
                    <p class="col-xs-12">
                        <strong>Writer:</strong>
                        <span data-bind="text: details().writer"></span>
                    </p>
                    <p class="col-xs-12">
                        <strong>Director:</strong>
                        <span data-bind="text: details().director"></span>
                    </p>
                    <p class="col-xs-12">
                        <strong>Fun Facts:</strong>
                        <span data-bind="text: details().fun_facts"></span>
                    </p>
                    <p class="col-xs-12">
                        <strong>Year:</strong>
                        <span data-bind="text: yearFormatted"></span>
                    </p>
                    <div class="col-xs-12">
                        <strong>Actors:</strong>
                        <ul data-bind="foreach: details().actors">
                            <li>
                                <span data-bind="text: first"></span> <span data-bind="text: last"></span>
                            </li>
                        </ul>
                        <strong>Locations:</strong>
                        <ul data-bind="foreach: details().locations">
                            <li>
                                <span data-bind="text: description"></span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="phone-factor-hidden" class="hidden-xs"></div>

    @if($isTestMode)
        <br>
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div id="qunit"></div>
                    <div id="qunit-fixture"></div>
                </div>
            </div>
        </div>
        <br>
    @endif
</body>
</html>