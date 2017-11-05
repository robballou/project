<?php

namespace Project\Command\Deploy;

use Project\Base\ProjectCommand;
use Project\ArrayObjectWrapper;
use Project\Executor\Executor;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * List available deployments.
 * 
 * @codeCoverageIgnore
 */
class ListCommand extends ProjectCommand {
  protected function configure() {
    $this
      // the name of the command (the part after "bin/console")
      ->setName('deploy:list')

      // the short description shown while running "php bin/console list"
      ->setDescription('List available deployments')
    ;
  }

  /**
   * Deploy things
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $config = $this->getApplication()->config;
    $combined_items = [];

    $items = $config->getConfigOption('deploy');
    if ($items) {
      foreach ($items->getKeys() as $key) {
        $combined_items[] = $key;
      }
    }

    sort($combined_items);
    foreach ($combined_items as $item) {
      $output->writeln($item);
    }
  }

}
