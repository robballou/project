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

    $extra_args_array = [];
    foreach ($extra_args as $arg) {
      $extra_args_array += explode(' ', $arg);
    }
    
    $command = $details->get(['script', 'command']);
    $base = $details->get('base');
    if ($command) {
      if ($base) {
        $command = 'cd ' . escapeshellarg($base) . ' && ' . $command;
      }
      if ($extra_args_array) {
        array_shift($extra_args_array);
        $extra_args_str = implode(' ', $extra_args_array);
        if ($extra_args_str) {
          $command .= ' ' . $extra_args_str;
        }
        $extra_args_array = [];
        $extra_args_str = '';
      }
      $command = ' /bin/bash -c "' . $command . '"';
    }

    if (is_array($extra_args_array)) {
      $extra_args_str = implode(' ', $extra_args_array);
      if ($extra_args_str) {
        $extra_args_str = ' ' . $extra_args_str;
      }
    }
    return $this_command . ' exec ' . escapeshellarg($container) . $command . $extra_args_str;
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
