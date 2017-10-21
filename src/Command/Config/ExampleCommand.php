<?php

namespace Project\Command\Config;

use Project\Base\ProjectCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Outputs an example configuration.
 */
class ExampleCommand extends ProjectCommand {
  protected function configure() {
    $this
      // the name of the command (the part after "bin/console")
      ->setName('config:example')

      // the short description shown while running "php bin/console list"
      ->setDescription('Output an example configuration')

      ->addArgument('example', InputArgument::REQUIRED, 'The example command config to output')
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $dir = __DIR__;
    $examples_directory = dirname(dirname(dirname($dir))) . '/examples';

    $raw_filename = $input->getArgument('example');
    if (!preg_match('/^[A-Za-z-_]+$/', $raw_filename)) {
      throw new \Exception('Invalid example: ' . $raw_filename);
    }

    $filename = $examples_directory . '/' . $input->getArgument('example') . '.yml';
    if (file_exists($filename)) {
      foreach (file($filename) as $line) {
        $output->write($line);
      }
    }
  }

}
