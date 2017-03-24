<?php

namespace Project\Command\Config;

use Project\Base\ProjectCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Creates a new configuration file if one is not present.
 */
class CreateCommand extends ProjectCommand {
  protected function configure() {
    $this
      // the name of the command (the part after "bin/console")
      ->setName('config:create')

      // the short description shown while running "php bin/console list"
      ->setDescription('Create a configuration file in the current directory')
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $current = $this->getCurrentDirectory();

    $dir = $current . '/.project';
    if (!is_dir($dir)) {
      mkdir($dir, 0775);
    }

    $file = $dir . '/config.yml';
    if (!is_file($file)) {
      file_put_contents($file, "\n");
    }

    $this->outputVerbose($output, 'Created ' . $file);
  }

}
