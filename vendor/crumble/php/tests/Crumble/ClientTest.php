<?php

namespace Crumble\OAuth {
  function curl_setopt($ch, $option, $value) {
    return \CrumbleTest\ClientTest::$functions->curl_setopt($option, $value);
  }

  function curl_exec($ch) {
    return \CrumbleTest\ClientTest::$functions->curl_exec();
  }
}

namespace CrumbleTest {
  use PHPUnit\Framework\TestCase;
  use Mockery;

  use Crumble\Client;
  use Crumble\RequestTokenStore;


  class ClientTest extends TestCase {
    public static $functions;

    private $client;

    protected function setUp() {
      self::$functions = Mockery::spy()->makePartial();

      $this->client = new Client("http://localhost", "password", [
          "username" => "user",
          "password" => "pass"
      ]);
      $this->client->setTokenStore(new RequestTokenStore());
    }

    protected function tearDown() {
      Mockery::close();
    }

    public function testOAuthTokenRequest() {
      self::$functions->shouldReceive("curl_exec")->andReturn(json_encode([
          "meta" => [
              "status" => 200
          ],
          "data" => [
              "access_token" => "dummy"
          ]
      ]));

      $this->client->obtainAccessTokenIfRequried();

      self::$functions->shouldHaveReceived("curl_setopt")->with(CURLOPT_URL,
          "http://localhost/oauth/token?username=user&password=pass&grant_type=password&client_id=crubmle-angular2-client");
      self::$functions->shouldHaveReceived("curl_setopt")->with(CURLOPT_FOLLOWLOCATION, 1);
      self::$functions->shouldHaveReceived("curl_setopt")->with(CURLOPT_RETURNTRANSFER, 1);
      self::$functions->shouldHaveReceived("curl_setopt")->with(CURLOPT_HEADER, 0);

      $this->assertTrue($this->client->isAuthenticated());
    }
  }
}