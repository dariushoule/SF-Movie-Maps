<?php

class HomeController extends BaseController {

    /**
     * Serve the main application template. Includes frontend test files when on non-prod environments.
     *
     * @return View
     */
	public function appMain()
	{
        $params = array(
            'jsFiles' => array(
                'js/jquery.min.js',
                'js/bootstrap.min.js',
                'js/knockout.js',
                'js/lokijs.js', 'js/app.js'
            ),
            'cssFiles' => array(
                'css/bootstrap.min.css',
                'css/bootstrap-theme.min.css',
                'css/app.css'
            ),
            'isTestMode' => ((App::environment('local') || App::environment('testing'))
                                                        && Input::get('testing') === "true")
        );

        if($params['isTestMode']) {
            $params['jsFiles'][] = 'js/qunit.js';
            $params['jsFiles'][] = 'js/test.js';
            $params['cssFiles'][] = 'css/qunit.css';
        }

		return View::make('homepage')->with($params);
	}

}
