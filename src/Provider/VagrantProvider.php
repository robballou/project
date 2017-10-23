<?php

namespace Project\Provider;

use Project\ArrayObjectWrapper;
use Project\Provider\CommandProvider;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Provider for shell interface.
 */
class VagrantProvider extends CommandProvider {
  static public $styles = ['vagrant',];

  /**
   * {@inheritDoc}
   */
  public function get(InputInterface $input, OutputInterface $output, ArrayObjectWrapper $details = NULL, $subcommand = 'exec') {
    $environment = $this->config->getEnvironment($input);

    $this_command = 'vagrant';

    return $this->subcommand($input, $output, $details, $subcommand, $this_command);
  }

  /**
   * Handle connect commands.
   */
  public function subcommandConnect(InputInterface $input, OutputInterface $output, ArrayObjectWrapper $details, $this_command) {
    return $this_command . ' ssh';
  }

  /**
   * Handle general exec commands.
   */
  public function subcommandExec(InputInterface $input, OutputInterface $output, ArrayObjectWrapper $details, $this_command, $further_command) {
    return $this_command . ' ssh "' . $further_command . '"';
  }

}
