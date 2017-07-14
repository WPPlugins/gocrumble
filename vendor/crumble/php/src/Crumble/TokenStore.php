<?php
namespace Crumble;

interface TokenStore {
  public function store($token);

  public function fetch();

  public function delete();
}