<?php

namespace Project\Provider;

use Project\ArrayObjectWrapper;
use Project\Provider\CommandProvider;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Provider for shell interface.
 */
class DockerComposeProvider extends CommandProvider {
  static public $styles = ['docker',];

  /**
   * {@inheritDoc}
   */
  public function get(InputInterface $input, OutputInterface $output, ArrayObjectWrapper $details = NULL, $subcommand = 'exec') {
    $environment = $this->config->getEnvironment($input);

    $file = $details->get('file');
    $container = $details->container;
    if (!$container) {
      throw new \Exception('No container is set for this environment');
    }
    $this_command = 'docker-compose';
    if ($file) {
      $this_command .= ' -f ' . $file;
    }

    return $this->subcommand($input, $output, $details, $subcommand, $this_command);
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
  public function subcommandExec(InputInterface $input, OutputInterface $output, ArrayObjectWrapper $details, $this_command) {
    $container = $details->container;
    if (!$container) {
      throw new \Exception('No container is set for this environment');
    }

    return $this_command . ' exec ' . escapeshellarg($container);
  }


}