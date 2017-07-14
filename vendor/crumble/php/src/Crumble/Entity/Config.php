<?php

namespace Crumble\Entity;

class Config {
	private $client;

	public function __construct(\Crumble\Client $client) {
		$this->client = $client;
	}

	public function get() {
	  return $this->client->request("GET", "/v1/config");
	}
}