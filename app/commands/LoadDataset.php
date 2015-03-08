<?php

use Illuminate\Console\Command;
use MovieMaps\DataIngestion\DataSFTitleLoader;

class LoadDataset extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'load:titles';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Ingests SF movie map data from data source.';

	/**
	 * Create a new command instance.
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
        $loader = new DataSFTitleLoader("https://data.sfgov.org/resource/yitu-d5am.json");
        if(!$loader->retrieveData()) {
            $this->error('Unable to retrieve dataset');
            return;
        }

        if(!$loader->prepareLoad()) {
            $this->error('Unable to process the dataset: ' . $loader->getError());
            return;
        }

        if(!$loader->performLoad()) {
            $this->error('Unable to save the dataset to the database: ' . $loader->getError());
            return;
        }
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array();
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array();
	}

}
