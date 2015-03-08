<?php

class HomeControllerTest extends TestCase {


	public function testGetIndex()
	{
		$this->client->request('GET', '/');
		$this->assertTrue($this->client->getResponse()->isOk());
	}

}
