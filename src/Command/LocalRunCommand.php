<?php

namespace Project\Command;

use Project\Base\ProjectCommand;
use Project\Executor\Executor;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class LocalRunCommand extends ProjectCommand {
  protected function configure() {
    $this
      // the name of the command (the part after "bin/console")
      ->setName('local:run')

      // the short description shown while running "php bin/console list"
      ->setDescription('Run the local environment')

      ->addArgument('thing', InputArgument::IS_ARRAY | InputArgument::OPTIONAL, 'Optional thing(s) to run')
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $config = $this->getApplication()->config;
    $things = $input->getArgument('thing');
    if (!$things) {
      $things = $config->getConfigOption('local.components');
    }

    if (!$things) {
      $output->writeln('No things to run');
      return;
    }

    if ($this->isNonAssocativeArray($things)) {

    }

    $output->writeln('Running: ' . implode(', ', $things));
    foreach ($things as $thing) {
      // for strings, we assume there is a directory that matches...
      if (is_string($thing)) {
        print $thing;
      }
    }
  }
}
