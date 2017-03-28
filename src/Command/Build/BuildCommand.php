<?php

namespace Project\Command\Build;

use Project\Base\ProjectCommand;
use Project\ArrayObjectWrapper;
use Project\Executor\Executor;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class BuildCommand extends ProjectCommand {
  protected function configure() {
    $this
      // the name of the command (the part after "bin/console")
      ->setName('build')

      // the short description shown while running "php bin/console list"
      ->setDescription('Build tools support')

      ->addArgument('thing', InputArgument::IS_ARRAY | InputArgument::OPTIONAL, 'Optional thing(s) to build')
      ->addOption('all', null, InputOption::VALUE_NONE, 'Build all of the things')
    ;
  }

  /**
   * Build things
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $config = $this->getApplication()->config;
    $things = $input->getArgument('thing');

    if (!$config->getConfigOption('build')) {
      throw new \Exception('This project is not configured with any build options');
    }

    if ($input->getOption('all')) {
      $things = $config->getConfigOption(['local.components']);
    }

    // if the user did not specify things, try to find some
    if (!$things) {
      $things = $config->getConfigOption(['local.components.default']);
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

    if (is_array($things)) {
      $new_things = [];
      foreach ($things as $thing) {
        $new_things[$thing] = $config->getConfigOption('build.' . $thing);
      }
      $things = new ArrayObjectWrapper($new_things);
    }

    if (!$things) {
      throw new \Exception('No things to build');
    }

    $this->outputVerbose($output, 'Running: ' . implode(', ', $things->getKeys()));
    foreach ($things as $key => $thing) {
      $style = $thing->style;
      if (!$style && isset($thing->script)) {
        $style = 'script';
      }
      elseif (!$style && isset($thing->command)) {
        $style = 'command';
      }

      $command = [];
      if ($style == 'script' || $style == 'command') {
        if (isset($thing->base)) {
          $command[] = 'cd ' . $this->validatePath($thing->base);
        }

        if ($style == 'script') {
          $command[] = $this->replacePathVariables($thing->script, $config);
        }
        else {
          $command[] = $this->replacePathVariables($thing->command, $config);
        }
      }

      if ($command) {
        $ex = new Executor(implode(' && ', $command));
        if ($this->isVerbose($output)) {
          $ex->outputCommand($output);
        }
        $ex->execute();
      }
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
      'script' => 'Project\Runner\ScriptRunner',
      'command' => 'Project\Runner\CommandRunner',
    ];
    if (in_array($style, array_keys($map))) {
      return $map[$style];
    }

    return NULL;
  }

}
