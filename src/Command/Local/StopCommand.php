<?php

namespace Project\Command\Local;

use Project\Base\ProjectCommand;
use Project\ArrayObjectWrapper;
use Project\Executor\Executor;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class StopCommand extends ProjectCommand {
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
    $things = $input->getArgument('thing');

    // if the user did not specify things, try to find some
    if (!$things) {
      $things = $config->getConfigOption('local.components');
      if (!$things) {
        $things = $config->getConfigOption('local.default');
        if ($things) {
          $things = new ArrayObjectWrapper(['default' => $things]);
        }
      }
    }

    if (!$things) {
      $output->writeln('No things to run');
      return;
    }

    $this->outputVerbose($output, 'Running: ' . implode(', ', $things->getKeys()));
    foreach ($things as $key => $thing) {
      $runner_class = $this->getRunner($thing);
      $runner = new $runner_class($config, $thing, $input, $output);
      $runner->stop();
      $this->outputVerbose($output, 'Started: ' . $key);
    }
  }

  protected function getRunner($thing) {
    $style = $thing->style;

    if (isset($thing->runner)) {
      return $thing->runner;
    }

    $map = [
      'vagrant' => 'Project\Runner\VagrantRunner',
      'docker-compose' => 'Project\Runner\DockerComposeRunner',
      'docker' => 'Project\Runner\DockerRunner',
    ];
    if (in_array($style, array_keys($map))) {
      return $map[$style];
    }

    return NULL;
  }

}
