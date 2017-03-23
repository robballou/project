<?php

namespace Project\Command\Build;

use Project\Base\ProjectCommand;
use Project\ArrayObjectWrapper;
use Project\Executor\Executor;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ListCommand extends ProjectCommand {
  protected function configure() {
    $this
      // the name of the command (the part after "bin/console")
      ->setName('build:list')

      // the short description shown while running "php bin/console list"
      ->setDescription('List available build tools/commands')
    ;
  }

  /**
   * Build things
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $config = $this->getApplication()->config;
    $items = $config->getConfigOption('build');
    if ($items) {
      foreach ($items->getKeys() as $key) {
        $output->writeln($key);
      }
    }
  }

}
