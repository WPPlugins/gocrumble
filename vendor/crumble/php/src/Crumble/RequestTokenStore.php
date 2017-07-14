<?php

namespace Crumble;

class RequestTokenStore implements TokenStore {
  private $token;
  
  public function store($token) {
    $this->token = $token;
  }

  public function fetch() {
    return $this->token;
  }

  public function delete() {
    $this->token = NULL;
  }
}