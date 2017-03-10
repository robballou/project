<?php

namespace Project\Command;

use Project\Base\ProjectCommand;
use Project\ArrayObjectWrapper;
use Project\Executor\Executor;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class TestCommand extends ProjectCommand {
  protected function configure() {
    $this
      // the name of the command (the part after "bin/console")
      ->setName('test')
      // the short description shown while running "php bin/console list"
      ->setDescription('Connect via ssh/bash to the local site')
      ->addArgument('test', InputArgument::IS_ARRAY | InputArgument::OPTIONAL, 'Optional list of tests to run')
    ;
  }

  /**
   * Run tests
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $config = $this->getApplication()->config;
    $environment = $config->getEnvironment($input);

    // get the first one of the following options to figure out the style of
    // connection...
    $style = $config->getConfigOption([
      'test.' . $environment . '.style',
      'test.style',
    ]);

    $tests = $config->getConfigOption('test.' . $environment . '.tests');
    if (!$tests) {
      $tests = [];
    }
    $global_tests = $config->getConfigOption('test.tests');
    if ($global_tests) {
      if (is_object($global_tests)) {
        $global_tests = $global_tests->getArray();
      }
      if (is_object($tests)) {
        $tests = $tests->getArray();
      }
      $tests = new ArrayObjectWrapper(array_merge($tests, $global_tests));
    }

    $commands = [];

    switch ($style) {
      // Run tests on docker
      case 'docker-compose':
        foreach ($tests as $test => $details) {
          $test_command = '';
          if (isset($details->base)) {
            $test_command = 'cd ' . $details->base . ' && ';
          }
          $test_command .= $details->command;
          $this_command = 'docker-compose exec drupal /bin/bash -c "' . $test_command . '"';
          $commands[$test] = $this_command;
        }
        break;

      // Run tests on vagrant...
      case 'vagrant':
        $vagrant_directory = $config->getConfigOption([
          'connect.' . $environment . '.vagrant_directory',
          'connect.vagrant_directory',
          'local.' . $environment . '.vagrant_directory',
          'local.vagrant_directory',
        ]);

        foreach ($tests as $test => $details) {
          $test_command = '';
          if (isset($details->base)) {
            $test_command = 'cd ' . $details->base . ' && ';
          }
          $test_command .= $details->command;
          $this_command = 'cd ' . $vagrant_directory . ' && vagrant ssh "' . $test_command . '"';
          $commands[$test] = $this_command;
        }
        break;
    }

    foreach ($commands as $test_name => $this_command) {
      $output->writeln('Running test: ' . $test_name);
      $ex = new Executor($this_command);
      if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
        $ex->outputCommand($output);
      }
      $ex->execute();
    }
  }

}
