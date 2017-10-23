<?php

namespace Project\Provider;

use Project\Configuration;
use Project\ArrayObjectWrapper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Build a command specifically for some tool.
 */
abstract class CommandProvider {
  use \Project\Traits\PathTrait;

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

  /**
   * Get the name of the subcommand method.
   */
  protected function getSubcommandFunction($subcommand) {
    $sub = array_map('ucfirst', explode('_', $subcommand));
    return 'subcommand' . implode('', $sub);
  }

  /**
   * Set one of the things.
   */
  public function set($thing) {
    $this->things[] = $thing;
    return $this;
  }

  public function subcommand(InputInterface $input, OutputInterface $output, ArrayObjectWrapper $details, $subcommand, $this_command) {
    $subcommand_function = $this->getSubcommandFunction($subcommand);
    if (!method_exists($this, $subcommand_function)) {
      throw new \Exception('The docker-compose command provider does not have a subcommand: ' . $subcommand);
    }

    // build an array of arguments of the 4 that we need and then any remaining
    // args passed to this function.
    $args = [$input, $output, $details, $this_command];
    $function_args = func_get_args();
    if (count($function_args) > 5) {
      $args = $args + array_slice($function_args, 5);
    }

    return call_user_func_array([$this, $subcommand_function], $args);
  }

}
