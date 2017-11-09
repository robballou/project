<?php

namespace Project\Command\Build;

use Project\Base\BuildBaseCommand;
use Project\ArrayObjectWrapper;
use Project\Executor\Executor;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class BuildCommand extends BuildBaseCommand {
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

    $additional_options = $this->getAdditionalBuildOptions();
    if (!$config->getConfigOption('build') && empty($additional_options)) {
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
        if (!$new_things[$thing] && isset($additional_options->$thing)) {
          $new_things[$thing] = $additional_options->$thing;
        }
      }
      $things = new ArrayObjectWrapper($new_things);
    }

    if (!$things) {
      throw new \Exception('No things to build');
    }

    $this->outputVerbose($output, 'Running: ' . implode(', ', $things->getKeys()));
    foreach ($things as $key => $thing) {
      if (!$thing) {
        throw new \Exception('Could not find thing: ' . $key);
      }
      $style = $thing->get('style');
      if (!$style && isset($thing->script)) {
        $style = 'script';
      }
      elseif (!$style && isset($thing->command)) {
        $style = 'command';
      }

      if (!$style) {
        throw new \Exception('No build style found: ' . json_encode($thing));
      }

      $provider = $this->getCommandProvider($style);
      $command = $provider->get($input, $output, $thing);
      $ex = $this->getExecutor($command, $output);
      if ($this->isVerbose($output)) {
        $ex->outputCommand($output);
      }
      $ex->execute();

      // $command = [];
      // if ($style == 'script' || $style == 'command') {
      //   if (isset($thing->base)) {
      //     $command[] = 'cd ' . $this->validatePath($thing->base, $config);
      //   }

      //   if ($style == 'script') {
      //     $command[] = $this->replacePathVariables($thing->script, $config);
      //   }
      //   else {
      //     $command[] = $this->replacePathVariables($thing->command, $config);
      //   }
      // }

      // if ($command) {
      //   $ex = new Executor(implode(' && ', $command));
      //   if ($this->isVerbose($output)) {
      //     $ex->outputCommand($output);
      //   }
      //   $ex->execute();
      // }
    }
  }

}
