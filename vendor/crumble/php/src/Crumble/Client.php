<?php

namespace Crumble;

use Crumble\Exception\ForbiddenException;

class Client extends \Crumble\OAuth\Client {
  private $authorizationType = "Bearer";
  private $tokenStore;

  private $grantType = "password";
  private $grantParameters = [];

  function __construct($endpoint, $grantType, $grantParameters = []) {
    parent::__construct($endpoint);

    $this->tokenStore = new \Crumble\SessionTokenStore();
    $this->grantType = $grantType;
    $this->grantParameters = $grantParameters;
  }

  public function setTokenStore($tokenStore) {
    $this->tokenStore = $tokenStore;
  }

  public function getTokenStore() {
    return $this->tokenStore;
  }

  public function setGrantParameters($grantParameters) {
    $this->grantParameters = $grantParameters;
  }

  public function setAuthorizationType($authorizationType) {
    $this->authorizationType = $authorizationType;
  }

  public function obtainAccessTokenIfRequried() {
    if (empty($this->tokenStore->fetch())) {
      $response = $this->getAccessToken($this->grantType, $this->grantParameters);
      $response = $this->handleResponse($response);

      if (!empty($response) && $response->meta->status === 200) {
      	$this->tokenStore->store($response->data);
      }
      return $response;
    }
    return NULL;
  }

  public function isAuthenticated() {
    return $this->tokenStore->fetch() != NULL;
  }

  public function request($method, $url, $body = "", $queryParams = []) {
    $this->obtainAccessTokenIfRequried();
    $headers = [];

    if ($this->isAuthenticated()) {
      array_push($headers, "Authorization: " . $this->authorizationType . ' ' . $this->tokenStore->fetch()->access_token);
    }

    $response = $this->internalExecute($method,
      $this->absoluteUrl($url, $queryParams),
      $body,
      $headers);

    if (!empty($response)) {
    	return $this->handleResponse($response);
    }
    return $response;
  }

  public function Entity($entityName) {
   $reflClass = new \ReflectionClass("Crumble\\Entity\\" . $entityName);
   return $reflClass->newInstanceArgs([$this]);
  }

  private function handleResponse($response) {
  	$status = $response->meta->status;
  	if ($status == 401 || $status == 403) {
  		$this->accessToken = null;
  		throw new ForbiddenException($response);
  	}
  	return $response;
  }

}