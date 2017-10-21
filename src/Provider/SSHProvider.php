<?php

namespace Project\Provider;

use Project\ArrayObjectWrapper;
use Project\Provider\CommandProvider;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Provider for shell interface.
 */
class SSHProvider extends CommandProvider {
  static public $styles = ['ssh',];

  /**
   * {@inheritDoc}
   */
  public function get(InputInterface $input, OutputInterface $output, ArrayObjectWrapper $details = NULL, $subcommand = 'exec') {
    $environment = $this->config->getEnvironment($input);

    $this_command = 'ssh';

    $host = $details->get([
      $environment . '.host',
      'host',
    ]);
    if (!$host) {
      throw new \Exception('No host found for this environment');
    }

    $user = $details->get([
      $environment . '.user',
      'user',
    ]);

    if ($user) {
      $host = $user . '@' . $host;
    }

    $identity_file = $user = $details->get([
      $environment . '.identity_file',
      'identity_file',
    ]);

    if ($identity_file) {
      $this_command .= ' -i ' . escapeshellarg($identity_file);
    }

    $this_command .= ' ' . escapeshellarg($host);

    return $this->subcommand($input, $output, $details, $subcommand, $this_command);
  }

  public function subcommandConnect(InputInterface $input, OutputInterface $output, ArrayObjectWrapper $details, $this_command) {
    $environment = $this->config->getEnvironment($input);
    $sub_command = 'bash --login';
    $base = $this->config->getConfigOption(['connect.' . $environment . '.base',
      'connect.base',
      'local.' . $environment . '.base',
      'local.base',
    ]);
    if ($base) {
      $sub_command = 'cd ' . $base . '; ' . $sub_command;
    }

    return $this_command . ' -t "' . $sub_command . '"';
  }

  public function subcommandExec(InputInterface $input, OutputInterface $output, ArrayObjectWrapper $details, $this_command) {
    //     $sub_command = 'bash --login';
    //     $base = $config->getConfigOption(['connect.' . $environment . '.base',
    //       'connect.base',
    //       'local.' . $environment . '.base',
    //       'local.base',
    //     ]);
    //     if ($base) {
    //       $sub_command = 'cd ' . $base . '; ' . $sub_command;
    //     }
    //     $this_command = 'ssh ' . $host . ' -t "' . $sub_command . '"';
    return $this_command . escapeshellarg($details->command);
  }


}
