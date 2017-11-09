<?php

namespace Project\Command\Build;

use Project\Base\BuildBaseCommand;
use Project\ArrayObjectWrapper;
use Project\Executor\Executor;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ListCommand extends BuildBaseCommand {
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
    $options = [];
    if ($items = $config->getConfigOption('build')) {
      foreach ($items->getKeys() as $key) {
        $options[] = $key;
      }
    }

    $additional_build_options = $this->getAdditionalBuildOptions();
    if ($additional_build_options) {
      foreach ($additional_build_options->getKeys() as $key) {
        if (!in_array($key, $options)) {
          $options[] = $key;
        }
      }
    }

    sort($options);

    foreach ($options as $key) {
      $output->writeln($key);
    }
  }

}
