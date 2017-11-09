<?php

namespace Project\Provider;

use Project\Provider\CommandProvider;
use Project\ArrayObjectWrapper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AnsibleProvider extends CommandProvider {
  static public $styles = ['ansible'];

  /**
   * {@inheritDoc}
   */
  public function get(InputInterface $input, OutputInterface $output, ArrayObjectWrapper $details = NULL) {
    if (!$details) {
      $details = new ArrayObjectWrapper();
    }

    $this_command = '';
    if (isset($details->base)) {
      $path = $this->validatePath($details->base, $this->config);
      if (!$path) {
        throw new \Exception('Could not validate the base path for this script: ' . $details->base);
      }
      $this_command = 'cd ' . escapeshellarg($path) . ' && ';
    }

    $this_command .= 'ansible-playbook ';

    if (isset($details->inventory)) {
      $this_command .= '-i ' . escapeshellarg($details->inventory) . ' ';
    }

    if (!isset($details->file)) {
      throw new \Exception('Ansible requires a playbook file. Please add a "file" to the configuration.');
    }

    $this_command .= escapeshellarg($details->file);
    return $this_command;
  }
}
