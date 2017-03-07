<?php

namespace Project\Command;

use Project\Command\ProjectCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class LocalRunCommand extends ProjectCommand {
  protected function configure() {
    $this
      // the name of the command (the part after "bin/console")
      ->setName('local:run')

      // the short description shown while running "php bin/console list"
      ->setDescription('Run the Drupal site locally')

      ->addArgument('thing', InputArgument::IS_ARRAY | InputArgument::OPTIONAL, 'Optional thing(s) to run')
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $things = $input->getArgument('thing');
    if (!$things) {
      $things = $this->getConfigOption('local.apps');
    }

    if (!$things) {
      $output->writeln('No things to run');
      return;
    }

    $output->writeln('Running: ' . implode(', ', $things));
    var_dump($input);
    foreach ($things as $thing) {
      // for strings, we assume there is a directory that matches...
      if (is_string($thing)) {
        print $thing;
      }
    }
  }
}
