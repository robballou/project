<?php

namespace Project\Command\Local;

use Project\Base\LocalBaseCommand;
use Project\ArrayObjectWrapper;
use Project\Executor\Executor;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class EnvCommand extends LocalBaseCommand {
  protected function configure() {
    $this
      // the name of the command (the part after "bin/console")
      ->setName('local:env')

      // the short description shown while running "php bin/console list"
      ->setDescription('Set an environment variable (or variables)')
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $config = $this->getApplication()->config;
    $things = $this->resolveThings($input, $output);

    var_dump($things);
  }

}
