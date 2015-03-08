<?php

use MovieMaps\DataIngestion\DataSFTitleLoader;

class TestCase extends Illuminate\Foundation\Testing\TestCase {

	/**
	 * Creates the application.
	 *
	 * @return \Symfony\Component\HttpKernel\HttpKernelInterface
	 */
	public function createApplication()
	{
		$unitTesting = true;
		$testEnvironment = 'testing';
		return require __DIR__.'/../../bootstrap/start.php';
	}

    public function setUp()
    {
        parent::setUp();
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Schema::dropIfExists('title');
        Schema::dropIfExists('title_actor');
        Schema::dropIfExists('title_location');
        Schema::dropIfExists('actor');
        Schema::dropIfExists('location');
        Schema::dropIfExists('migrations');
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
        Artisan::call('migrate');
    }

    public function loadHappyPathData() {
        $loader = new DataSFTitleLoader(__DIR__ . '/sample-inputs/happy-path.json');
        $loader->retrieveData();
        $loader->prepareLoad();
        $loader->performLoad();
    }
}
