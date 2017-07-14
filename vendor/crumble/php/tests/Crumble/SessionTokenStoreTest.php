<?php

namespace CrumbleTest;

use PHPUnit\Framework\TestCase;
use Crumble\SessionTokenStore;

class SessionTokenStoreTest extends TestCase {
  protected function setUp() {
    global $_SESSION;
  }

  public function testStore() {
    $store = new SessionTokenStore();
    $store->store("example");
    $this->assertEquals("example", $store->fetch());
  }

  public function testDelete() {
    $store = new SessionTokenStore();
    $store->store("example");
    $store->delete();

    $this->assertNull($store->fetch());
  }
}