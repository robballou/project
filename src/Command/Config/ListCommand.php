<?php

namespace Project\Command\Config;

use Project\Base\ProjectCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class ListCommand extends ProjectCommand {
  protected function configure() {
    $this
      // the name of the command (the part after "bin/console")
      ->setName('config:list')

      // the short description shown while running "php bin/console list"
      ->setDescription('Show the configuration for this context')
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $config = $this->getApplication()->config->getConfig();
    $output->write(yaml_emit($config));
  }
}
