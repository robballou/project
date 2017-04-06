<?php

namespace Project\Command\Local;

use Project\Base\LocalBaseCommand;
use Project\ArrayObjectWrapper;
use Project\Executor\Executor;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class StopCommand extends LocalBaseCommand {
  protected function configure() {
    $this
      // the name of the command (the part after "bin/console")
      ->setName('local:stop')
      ->setAliases(['local:halt', 'local:down'])

      // the short description shown while running "php bin/console list"
      ->setDescription('Stop the local environment')

      ->addArgument('thing', InputArgument::IS_ARRAY | InputArgument::OPTIONAL, 'Optional thing(s) to stop')
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $config = $this->getApplication()->config;

    $things = $this->resolveThings($input, $output);

    $this->outputVerbose($output, 'Running: ' . implode(', ', $things->getKeys()));
    foreach ($things as $key => $thing) {
      $runner_class = $this->getRunner($thing);
      $runner = new $runner_class($config, $thing, $input, $output);
      $runner->stop();
      $this->outputVerbose($output, 'Started: ' . $key);
    }
  }

}
