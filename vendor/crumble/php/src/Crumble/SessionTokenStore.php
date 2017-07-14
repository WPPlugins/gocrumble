<?php

namespace Crumble;

class SessionTokenStore implements TokenStore {
  const PARAMETER_NAME = "CrumbleToken";

  var $paramName = self::PARAMETER_NAME;

  public function setParamName($name) {
    $this->paramName = $name;
  }

  public function store($token) {
    global $_SESSION;

    $_SESSION[$this->paramName] = $token;
  }

  public function fetch() {
    global $_SESSION;

    if (isset($_SESSION[$this->paramName])) {
      return $_SESSION[$this->paramName];
    }
    return NULL;
  }

  public function delete() {
    global $_SESSION;

    unset($_SESSION[$this->paramName]);
  }
}