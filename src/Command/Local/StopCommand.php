<?php

namespace Project\Command\Local;

use Project\Base\LocalBaseCommand;
use Project\ArrayObjectWrapper;
use Project\Executor\Executor;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class StopCommand extends LocalBaseCommand {
  protected function configure() {
    $this
      // the name of the command (the part after "bin/console")
      ->setName('local:stop')
      ->setAliases(['local:halt', 'local:down'])

      // the short description shown while running "php bin/console list"
      ->setDescription('Stop the local environment')

      ->addOption('all', 'a', InputOption::VALUE_NONE, 'Stop all things')

      ->addArgument('thing', InputArgument::IS_ARRAY | InputArgument::OPTIONAL, 'Optional thing(s) to stop')
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $config = $this->getApplication()->config;

    $things = $this->resolveThings($input, $output);
    if (!$things) {
      throw new \Exception('Could not resolve the components for this command');
    }

    $this->outputVerbose($output, 'Stopping: ' . implode(', ', $things->getKeys()));
    foreach ($things as $key => $thing) {
      $this->outputVerbose($output, 'Stopping: ' . json_encode($thing, JSON_PRETTY_PRINT));

      $this->pre($input, $output, $thing);

      $style = $thing->get('style', NULL);
      $provider = $this->getCommandProvider($style);
      $this_command = $provider->get($input, $output, $thing, 'run');
      $ex = $this->getExecutor($this_command, $output);
      $ex->execute();

      $this->outputVerbose($output, 'Stopped: ' . $key);

      $this->post($input, $output, $thing);
    }
  }

}
