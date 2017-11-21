<?php

namespace Project\Provider;

use Project\ArrayObjectWrapper;
use Project\Provider\DockerProvider;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Provider for docker-compose
 */
class DockerComposeProvider extends DockerProvider {
  static public $styles = ['docker', 'docker-compose',];

  /**
   * Provide the correct command name.
   *
   * @param ArrayObjectWrapper $details
   * @return string
   */
  protected function command(ArrayObjectWrapper $details) {
    return 'docker-compose';
  }

  /**
   * Handle general exec commands.
   */
  protected function subcommandExec(InputInterface $input, OutputInterface $output, ArrayObjectWrapper $details, $this_command, $extra_args = []) {
    $container = $details->container;
    if (!$container) {
      throw new \Exception('No container is set for this environment. Expected a container to be specified in: ' . json_encode($details, JSON_PRETTY_PRINT));
    }
    
    $command = $details->get(['script', 'command']);
    $base = $details->get('base');
    if ($command) {
      if ($base) {
        $command = 'cd ' . escapeshellarg($base) . ' && ' . $command;
      }
      $command = ' /bin/bash -c "' . $command . '"';

      array_shift($extra_args);
    }

    $extra_args = implode(' ', $extra_args);
    if ($extra_args) {
      $extra_args = ' ' . $extra_args;
    }

    return $this_command . ' exec ' . escapeshellarg($container) . $command . $extra_args;
  }

  /**
   * Handle run commands.
   */
  public function subcommandRun(InputInterface $input, OutputInterface $output, ArrayObjectWrapper $details, $this_command) {
    $command = $this_command . ' up';
    $detached = $details->get(['daemon', 'detached'], true);
    if ($detached) {
      $command .= ' -d';
    }
    return $command;
  }
  
  /**
   * Handle run commands.
   */
  public function subcommandStop(InputInterface $input, OutputInterface $output, ArrayObjectWrapper $details, $this_command) {
    return $this_command . ' down';
  }
}
