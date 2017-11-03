<?php

namespace Project\Provider;

use Project\ArrayObjectWrapper;
use Project\Provider\CommandProvider;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Provider for shell interface.
 */
class DockerProvider extends CommandProvider {
  static public $styles = ['docker',];

  /**
   * {@inheritDoc}
   */
  public function get(InputInterface $input, OutputInterface $output, ArrayObjectWrapper $details = NULL, $subcommand = 'exec', ...$args) {
    $environment = $this->config->getEnvironment($input);

    $file = $details->get('file');
    $container = $details->container;
    if (!$container) {
      throw new \Exception('No container is set for this environment');
    }
    $this_command = $this->command($details);
    if ($file) {
      $this_command .= ' -f ' . $file;
    }

    return $this->subcommand($input, $output, $details, $subcommand, $this_command, $args);
  }

  /**
   * Provide the command name.
   */
  protected function command(ArrayObjectWrapper $details) {
    return $details->get('bin', 'docker');
  }

  /**
   * Handle connect commands.
   */
  public function subcommandConnect(InputInterface $input, OutputInterface $output, ArrayObjectWrapper $details, $this_command) {
    $this_command = $this->subcommandExec($input, $output, $details, $this_command);
    return $this_command . ' /bin/bash';
  }

  /**
   * Handle general exec commands.
   */
  public function subcommandExec(InputInterface $input, OutputInterface $output, ArrayObjectWrapper $details, $this_command, $extra_args = []) {
    $container = $details->container;
    if (!$container) {
      throw new \Exception('No container is set for this environment');
    }
    $extra_args = implode(' ', $extra_args);
    if ($extra_args) {
      $extra_args = ' ' . $extra_args;
    }
    return $this_command . ' exec ' . escapeshellarg($container) . $extra_args;
  }


}
