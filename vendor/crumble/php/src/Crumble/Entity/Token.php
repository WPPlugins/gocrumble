<?php

namespace Crumble\Entity;

class Token {
	private $client;

	public function __construct(\Crumble\Client $client) {
		$this->client = $client;
	}

	public function list($filter = []) {
	 return $this->client->request("GET", "/v1/tokens", "", $filter);
	}

	public function get($token) {
	  return $this->client->request("GET", "/v1/tokens/" . $token);
	}
}