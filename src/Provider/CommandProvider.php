<?php

namespace Project\Provider;

use Project\Configuration;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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

  /**
   * Get the given command.
   */
  abstract public function get(InputInterface $input, OutputInterface $output);

  public function set($thing) {
    $this->things[] = $thing;
    return $this;
  }

}
