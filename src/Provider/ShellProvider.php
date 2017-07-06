<?php

namespace Project\Provider;

use Project\ArrayObjectWrapper;
use Project\Provider\CommandProvider;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Provider for shell interface.
 */
class ShellProvider extends CommandProvider {

  static public $styles = ['local', 'shell', 'command', 'script'];

  /**
   * {@inheritDoc}
   */
  public function get(InputInterface $input, OutputInterface $output, ArrayObjectWrapper $details = NULL) {
    if (!$details) {
      $details = new ArrayObjectWrapper();
    }

    $this_command = '';
    if (isset($details->base) && $path = $this->validatePath($details->base, $this->config)) {
      $this_command = 'cd ' . escapeshellarg($path) . ' && ';
    }

    if (isset($details->command)) {
      $this_command .= $details->command;
    }
    elseif (isset($details->script)) {
      if (isset($details->executable)) {
        $this_command .= $details->executable . ' ';
      }
      else {
        $this_command .= './';
      }

      $this_command .= $details->script;
    }

    return $this_command;
  }

}
