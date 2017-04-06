<?php

namespace Project\Command\Local;

use Project\Base\LocalBaseCommand;
use Project\ArrayObjectWrapper;
use Project\Executor\Executor;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class DbImportCommand extends LocalBaseCommand {
  protected function configure() {
    $this
      // the name of the command (the part after "bin/console")
      ->setName('local:db:import')

      // the short description shown while running "php bin/console list"
      ->setDescription('Import a local file to the local DB')
      ->addArgument('file', InputArgument::REQUIRED, 'The file to import')
      ->addArgument('thing', InputArgument::IS_ARRAY | InputArgument::OPTIONAL, 'Optional thing(s) to update')
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $config = $this->getApplication()->config;

    $things = $this->resolveThings($input, $output);

    $this->outputVerbose($output, 'Importing to: ' . implode(', ', $things->getKeys()));
    foreach ($things as $key => $thing) {
      $this->outputVerbose($output, 'Importing to: ' . json_encode($thing, JSON_PRETTY_PRINT));

      // $runner_class = $this->getRunner($thing);
      // if (!$runner_class) {
      //   throw new \Exception('No runner for this thing: ' . json_encode($thing));
      // }
      // $runner = new $runner_class($config, $thing, $input, $output);
      // $runner->run();
      // $this->outputVerbose($output, 'Started: ' . $key);
    }
  }

}
