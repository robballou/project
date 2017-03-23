<?php

namespace Project\Command\Local;

use Project\Base\ProjectCommand;
use Project\ArrayObjectWrapper;
use Project\Executor\Executor;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class RestartCommand extends ProjectCommand {
  protected function configure() {
    $this
      // the name of the command (the part after "bin/console")
      ->setName('local:restart')
      ->setAliases(['local:reload'])

      // the short description shown while running "php bin/console list"
      ->setDescription('Restart the local environment')

      ->addArgument('thing', InputArgument::IS_ARRAY | InputArgument::OPTIONAL, 'Optional thing(s) to run')
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $config = $this->getApplication()->config;

    // stop local
    $this->outputVerbose($output, 'Stopping local');
    $command = $this->getApplication()->find('local:stop');
    $command->run($input, $output);

    // start local
    $this->outputVerbose($output, 'Starting local');
    $command = $this->getApplication()->find('local:start');
    $command->run($input, $output);
  }

}
