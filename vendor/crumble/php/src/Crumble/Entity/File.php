<?php

namespace Crumble\Entity;

class File {
  private $client;

  public function __construct(\Crumble\Client $client) {
    $this->client = $client;
  }

  public function download($ids) {
    $token = $this->client->getTokenStore()->fetch();

    return $this->client->absoluteUrl("/v1/files/" . join(",", $ids) . "/download", [
        "access_token" => $token->access_token
    ]);
  }
}