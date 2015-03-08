<?php

class ApiControllerTest extends TestCase {

    public function testPrecache()
    {
        $this->loadHappyPathData();
        $this->client->request('GET', '/titles-and-locations');
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('lat', $response[0], "We expect precache items to contain lat");
        $this->assertArrayHasKey('lng', $response[0], "We expect precache items to contain lng");
        $this->assertArrayHasKey('lat', $response[1], "We expect precache items to contain lat");
        $this->assertArrayHasKey('lng', $response[1], "We expect precache items to contain lng");

        $this->assertArraySubset(
            json_decode(file_get_contents(__DIR__ . '/sample-outputs/precache.json'), true),
            $response,
            "We expect the frontend to be able to understand our precache response"
        );
    }

    public function testTitleSearch() {
        $this->loadHappyPathData();
        $this->client->request('GET', '/title/' . Title::first()->title_id);
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('lat', $response['title'][0]['locations'][0], "We expect title responses to contain lat");
        $this->assertArrayHasKey('lng', $response['title'][0]['locations'][0], "We expect title responses to contain lng");
        $this->assertArrayHasKey('image_url', $response['title'][0], "We expect title responses to contain poster paths");
        $this->assertArrayHasKey('created_at', $response['title'][0], "We expect title responses to have a create date");
        $this->assertArrayHasKey('updated_at', $response['title'][0], "We expect title responses to have an update date");
        $this->assertArrayHasKey('checksum', $response['title'][0], "We expect title responses to have a checksum");

        $this->assertArraySubset(
            json_decode(file_get_contents(__DIR__ . '/sample-outputs/title.json'), true),
            $response,
            "We expect the frontend to be able to understand our title search response."
        );
    }

    public function testModified()
    {
        $this->client->request('GET', '/last-modified');
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $modifiedEmpty = $response['modified'];

        $this->assertEquals(0, $modifiedEmpty, "We expect last modified timestamp to be empty before load.");

        $this->loadHappyPathData();

        $this->client->request('GET', '/last-modified');
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $modified = $response['modified'];

        $this->assertNotEquals($modifiedEmpty, $modified, "We expect last modified timestamp to change after load.");
    }
}
