<?php

namespace Project\Command\Local;

use Project\Base\LocalBaseCommand;
use Project\ArrayObjectWrapper;
use Project\Executor\Executor;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class RunCommand extends LocalBaseCommand {
  protected function configure() {
    $this
      // the name of the command (the part after "bin/console")
      ->setName('local:run')
      ->setAliases(['local:start', 'local:up'])

      // the short description shown while running "php bin/console list"
      ->setDescription('Run the local environment')

      ->addOption('all', 'a', InputOption::VALUE_NONE, 'Run all things')

      ->addArgument('thing', InputArgument::IS_ARRAY | InputArgument::OPTIONAL, 'Optional thing(s) to run')
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $config = $this->getApplication()->config;

    $things = $this->resolveThings($input, $output);
    if (!$things) {
      throw new \Exception('Could not resolve the components for this command');
    }

    $this->outputVerbose($output, 'Running: ' . implode(', ', $things->getKeys()));
    foreach ($things as $key => $thing) {
      $this->outputVerbose($output, 'Running: ' . json_encode($thing, JSON_PRETTY_PRINT));

      $this->pre($input, $output, $thing);

      $runner_class = $this->getRunner($thing);
      if (!$runner_class) {
        throw new \Exception('No runner for this thing: ' . json_encode($thing));
      }
      $runner = new $runner_class($config, $thing, $input, $output);
      $runner->run();
      $this->outputVerbose($output, 'Started: ' . $key);

      $this->post($input, $output, $thing);
    }
  }

}
