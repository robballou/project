<?php

namespace Project\Provider;

use Project\ArrayObjectWrapper;
use Project\Provider\DockerProvider;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Provider for shell interface.
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
   * Handle run commands.
   */
  public function subcommandRun(InputInterface $input, OutputInterface $output, ArrayObjectWrapper $details, $this_command) {
    $this_command = $this->subcommandExec($input, $output, $details, $this_command);
    return $this_command . ' up';
  }
  
  /**
   * Handle run commands.
   */
  public function subcommandStop(InputInterface $input, OutputInterface $output, ArrayObjectWrapper $details, $this_command) {
    $this_command = $this->subcommandExec($input, $output, $details, $this_command);
    return $this_command . ' down';
  }
}
