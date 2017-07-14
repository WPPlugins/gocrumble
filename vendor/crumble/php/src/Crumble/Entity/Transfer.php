<?php

namespace Crumble\Entity;

class Transfer {
  private $client;

  public function __construct(\Crumble\Client $client) {
    $this->client = $client;
  }

  public function url($source) {
    $token = $this->client->getTokenStore()->fetch();
    return $this->client->absoluteUrl("/v1/transfers/" . $source, [
        "access_token" => $token->access_token
    ]);
  }

}