<?php

namespace Project\Command;

use Project\Base\ProjectCommand;
use Project\ArrayObjectWrapper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class PassAlongCommand extends ProjectCommand {

  protected function configure() {
    $this
      // the short description shown while running "php bin/console list"
      ->setDescription('Pass along the command to another context')
      ->ignoreValidationErrors()
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $config = $this->getApplication()->config;
    $environment = $config->getEnvironment($input);

    $command_name = $this->getName();
    $style = $config->get(['passalong.' . $command_name . '.style'], 'shell');
    $details = $config->get(['passalong.' . $command_name]);
    
    $command = explode(' ', (string) $input);
    $command = implode(' ', array_slice($command, array_search($command_name, $command)));

    $provider = $this->getCommandProvider($style);
    $this_command = $provider->get($input, $output, $details, 'exec', $command);

    $ex = $this->getExecutor($this_command, $output);
    $ex->execute();
  }
}
