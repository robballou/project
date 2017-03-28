<?php

namespace Project\Command\Local;

use Project\Base\ProjectCommand;
use Project\ArrayObjectWrapper;
use Project\Executor\Executor;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class RunCommand extends ProjectCommand {
  protected function configure() {
    $this
      // the name of the command (the part after "bin/console")
      ->setName('local:run')
      ->setAliases(['local:start', 'local:up'])

      // the short description shown while running "php bin/console list"
      ->setDescription('Run the local environment')

      ->addArgument('thing', InputArgument::IS_ARRAY | InputArgument::OPTIONAL, 'Optional thing(s) to run')
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
    elseif (is_array($things)) {
      $things = new ArrayObjectWrapper($things);
    }

    $processed_things = [];
    foreach ($things as $key => $thing) {
      if (is_string($thing)) {
        $processed_things[$thing] = $config->getConfigOption('local.components.' . $thing);
        continue;
      }
      $processed_things[$key] = $thing;
    }
    $things = new ArrayObjectWrapper($processed_things);

    $this->outputVerbose($output, 'Running: ' . implode(', ', $things->getKeys()));
    foreach ($things as $key => $thing) {
      $this->outputVerbose($output, 'Running: ' . json_encode($thing, JSON_PRETTY_PRINT));

      $runner_class = $this->getRunner($thing);
      if (!$runner_class) {
        throw new \Exception('No runner for this thing: ' . json_encode($thing));
      }
      $runner = new $runner_class($config, $thing, $input, $output);
      $runner->run();
      $this->outputVerbose($output, 'Started: ' . $key);
    }
  }

  protected function getRunner($thing) {
    $style = $thing->style;
    if (!$style) {
      throw new \Exception('Invalid style for this local component: ' . json_encode($thing));
    }

    if (isset($thing->runner)) {
      return $thing->runner;
    }

    $map = [
      'vagrant' => 'Project\Runner\VagrantRunner',
      'docker-compose' => 'Project\Runner\DockerComposeRunner',
      'docker' => 'Project\Runner\DockerRunner',
      'script' => 'Project\Runner\ScriptRunner',
      'command' => 'Project\Runner\CommandRunner',
    ];
    if (in_array($style, array_keys($map))) {
      return $map[$style];
    }

    return NULL;
  }

}
