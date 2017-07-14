<?php

namespace Crumble\OAuth;

class Client {
  private $tokenEndpoint = "/oauth/token";
  private $endpoint;
  private $defaultClientId = "crubmle-angular2-client";

  function __construct($endpoint) {
    $this->endpoint = $endpoint;
  }

  public function setDefaultClientId($clientId) {
    $this->defaultClientId = $clientId;
  }

  public function getAccessToken($grantType, $params) {
    $parameters = array_merge($params, [
      'grant_type' => $grantType
    ]);
    if (empty($parameters["client_id"])) {
      $parameters["client_id"] = $this->defaultClientId;
    }
    $url = $this->absoluteUrl($this->tokenEndpoint, $parameters);
    return $this->internalExecute('get', $url);
  }

  public function absoluteUrl($url, $queryString = array()) {
    $url = $this->endpoint . $url;
    $queryString = http_build_query($queryString);
    if (!empty($queryString)) {
      $url .= '?' . $queryString;
    }
    return $url;
  }

  protected function internalExecute($method, $url, $body = "", $headers = []) {
    $curl = curl_init();
    $headers = array_merge(["Content-Type: application/json"], $headers);

    if (!empty($body)) {
      $body = json_encode($body);
      array_push($headeres,  "Content-Length: " . strlen($body));
    }

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, strtoupper($method));
    curl_setopt($curl, CURLOPT_POSTFIELDS, $body);

    $result = curl_exec($curl);
    if (!empty($result)) {
      $result = json_decode($result);
    }

    curl_close($curl);
    return $result;
  }


}