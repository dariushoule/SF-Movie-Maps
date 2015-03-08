<?php

use MovieMaps\DataIngestion\DataSFTitleLoader;

class DataSFTitleLoaderTest extends TestCase {


	public function testLoadGoodJson()
	{
        $loader = new DataSFTitleLoader(__DIR__ . '/sample-inputs/happy-path.json');
        $result = $loader->retrieveData();
        $this->assertTrue($result,
            'We expect to be able to retrieve well formatted JSON');

        $result = $loader->prepareLoad();
        $this->assertTrue($result,
            'We expect to be able to understand well formatted JSON');
	}

    public function testLoadMalformattedJson()
    {
        $loader = new DataSFTitleLoader(__DIR__ . '/sample-inputs/malformed.json');
        $result = $loader->retrieveData();
        $this->assertFalse($result);
        $this->assertEquals($loader->getError(), DataSFTitleLoader::RETRIEVAL_ERROR,
            'We expect errors retrieving data to result in RETRIEVAL_ERROR');

        $result = $loader->prepareLoad();
        $this->assertFalse($result);
        $this->assertEquals($loader->getError(), DataSFTitleLoader::COLLAPSE_ERROR,
            'We expect errors collapsing data to result in COLLAPSE_ERROR');
    }

    public function testInsertNew()
    {
        $this->loadHappyPathData();

        $title = Title::with(array('actors', 'locations'))->get();

        $this->assertEquals(1, count($title), 'We expect the correct number of titles for this query');
        $title = $title[0];

        $this->assertEquals(3, count($title->actors), 'We expect the correct number of actors for this title');
        $this->assertEquals(2, count($title->locations), 'We expect the correct number of locations for this title');
        $this->assertEquals("Happy Gilmore", $title->name, 'We expect the correct name for this title');
        $this->assertEquals("Unknown", $title->distributor, 'We expect the correct director for this title');
        $this->assertEquals("SPI Cinemas", $title->producer, 'We expect the correct producer for this title');
        $this->assertEquals("Umarji Anuradha, Jayendra, Aarthi Sriram, & Suba", $title->writer, 'We expect the correct writer for this title');
        $this->assertEquals("Jayendra", $title->director, 'We expect the correct director for this title');
        $this->assertEquals("Unknown", $title->fun_facts, 'We expect the correct facts for this title');
        $this->assertEquals("2011-00-00", $title->year, 'We expect the correct year for this title');
        $this->assertEquals(3916106904, $title->checksum, 'We expect the correct checksum for this title');

        $imgFile = __DIR__ . '/../../public/' . $title->image_url;
        $this->assertFalse(is_dir($imgFile), "We expect the image to exist on disk: $imgFile");
        $this->assertTrue(file_exists($imgFile), "We expect the image to exist on disk: $imgFile");
        $this->assertGreaterThan(0, filesize($imgFile), "We expect the image to have non-zero size");
        unlink($imgFile);

        $this->assertEquals("Ralph", $title->actors[0]->first . $title->actors[0]->last,
            "We expect actors with only a first name to be inserted correctly");

        $this->assertEquals("Mickey Moose", $title->actors[1]->first . " " . $title->actors[1]->last,
            "We expect actors with first middle and last name to be inserted correctly");

        $this->assertEquals("Bubba Billy", $title->actors[2]->first . " " . $title->actors[2]->last,
            "We expect actors with only a first and last name to be inserted correctly");

        $this->assertEquals("Epic Roasthouse (399 Embarcadero)", $title->locations[0]->description,
            "We expect locations to have the correct description");

        $this->assertEquals("Justin Herman Plaza", $title->locations[1]->description,
            "We expect locations to have the correct description");

        $this->assertContains(array($title->locations[0]->lat, $title->locations[0]->lng),
            array(array(37.79926270, -122.39767320), array(37.78330000, -122.41670000)),
            "We expect locations to be geocoded correctly");

        $this->assertContains(array($title->locations[1]->lat, $title->locations[1]->lng),
            array(array(37.79434586, -122.39402008)),
            "We expect locations to be geocoded correctly");
    }

    public function testUpdateTitle()
    {
        $this->loadHappyPathData();

        $loader = new DataSFTitleLoader(__DIR__ . '/sample-inputs/update.json');
        $loader->retrieveData();
        $loader->prepareLoad();
        $loader->performLoad();

        $this->assertEquals(1, count(Actor::all()),
            'We expect no orphaned actor records after an update');
        $this->assertEquals(1, count(Location::all()),
            'We expect no orphaned location records after an update');

        $title = Title::with(array('actors', 'locations'))->get();

        $this->assertEquals(1, count($title), 'We expect the correct number of titles for this query after update');
        $title = $title[0];

        $this->assertEquals(1, count($title->actors), 'We expect the correct number of actors for this title after update');
        $this->assertEquals(1, count($title->locations), 'We expect the correct number of locations for this title after update');
        $this->assertEquals("Happy Gilmore", $title->name, 'We expect the correct name for this title after update');
        $this->assertEquals("Jiffy Lube Pictures", $title->distributor, 'We expect the correct director for this title after update');
        $this->assertEquals("Different Production Company", $title->producer, 'We expect the correct producer for this title after update');
        $this->assertEquals("Different Writers", $title->writer, 'We expect the correct writer for this title after update');
        $this->assertEquals("Different Director", $title->director, 'We expect the correct director for this title after update');
        $this->assertEquals("Elephants are the only animal that can fly using only the power of they're bad grammar.",
            $title->fun_facts, 'We expect the correct facts for this title after update');

        unlink(__DIR__ . '/../../public/' . $title->image_url);

        $this->assertEquals("2000-00-00", $title->year, 'We expect the correct year for this title after update');
        $this->assertEquals(3462749168, $title->checksum, 'We expect the correct checksum for this title after update');

        $this->assertEquals("Mickey Goose", $title->actors[0]->first . " " . $title->actors[0]->last,
            "We expect actor names to be updated correctly");

        $this->assertEquals("3355 Geary Blvd.", $title->locations[0]->description,
            "We expect locations to be updated correctly");
    }
}
