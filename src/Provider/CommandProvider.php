<?php

namespace Project\Provider;

use Project\Configuration;

/**
 * Build a command specifically for some tool.
 */
abstract class CommandProvider {
  protected $config;
  protected $things = [];

  /**
   * Constructor.
   */
  public function __construct(Configuration $config) {
    $this->config = $config;
  }

  abstract public function get();

  public function set($thing) {
    $this->things[] = $thing;
    return $this;
  }


}
