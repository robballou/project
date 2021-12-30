<?php

namespace Project\Command;

use Project\Base\ProjectCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Project\Executor\Executor;

/**
 * Connect to a terminal session on a container or remote environment.
 */
class ConnectCommand extends ProjectCommand {
  /**
   * {@inheritDoc}
   */
  protected function configure() {
    $this
      // the name of the command (the part after "bin/console")
      ->setName('connect')
      // the short description shown while running "php bin/console list"
      ->setDescription('Connect via ssh/bash to an environment')

      ->addArgument('name', InputArgument::OPTIONAL, 'Name of environment to connect to')
    ;
  }

  /**
   * {@inheritDoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $config = $this->getApplication()->config;
    $environment = $config->getEnvironment($input);
    $name = $input->getArgument('name');
    if ($name) {
      $environment = $name;
    }

    $command_config = $config->getCommandConfig('connect', $input);
    if ($command_config === NULL) {
      throw new \Exception('This project is not configured to use the connect command.');
    }

    // get the first one of the following options to figure out the style of
    // connection...
    $style = $config->getConfigOption([
      'connect.' . $environment . '.style',
      'connect.style',
      'local.' . $environment . '.style',
      'local.style',
    ]);

    $details = $config->getConfigOption([
      'connect.' . $environment,
      'connect',
      'local.' . $environment,
      'local',
    ]);

    $provider = $this->getCommandProvider($style);
    $this_command = $provider->get($input, $output, $details, 'connect');

    // better processing...
    $ex = $this->getExecutor($this_command);
    $ex->execute();
  }

}
