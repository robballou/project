<?php

namespace Project\Command\Config;

use Project\Command\ProjectCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class SourcesCommand extends ProjectCommand {
  protected function configure() {
    $this
      // the name of the command (the part after "bin/console")
      ->setName('config:sources')

      // the short description shown while running "php bin/console list"
      ->setDescription('Run the Drupal site locally')
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $config_files = $this->getApplication()->config->getConfigFiles();
    foreach ($config_files as $config_file) {
      $output->writeln($config_file);
    }
  }
}
