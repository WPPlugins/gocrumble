<?php

namespace Crumble\Entity;

class Contact {
	private $client;

	public function __construct(\Crumble\Client $client) {
		$this->client = $client;
	}

	public function me() {
	  return $this->client->request("GET", "/v1/contacts/me");
	}
}