<?php 

namespace Crumble\Exception;

class ForbiddenException extends \Exception {
  private $response;
  function __construct($response) {
    $this->response = $response;
  }
}
