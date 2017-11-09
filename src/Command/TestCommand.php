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
      ->setDescription('Run tests')
      ->addArgument('test', InputArgument::IS_ARRAY | InputArgument::OPTIONAL, 'Optional list of tests to run')
    ;
  }

  /**
   * Run tests
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $config = $this->getApplication()->config;
    $environment = $config->getEnvironment($input);

    $requested_tests = $input->getArgument('test');

    // get the first one of the following options to figure out the style of
    // connection...
    $style = $config->getConfigOption([
      'test.' . $environment . '.style',
      'test.style',
    ], 'local');

    $tests = $config->getConfigOption(['test.' . $environment . '.tests', 'test']);
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

    // filter tests
    if (!empty($requested_tests)) {
      $tests = array_filter($tests->getArray(), function ($key) use ($requested_tests) {
        return in_array($key, $requested_tests);
      }, ARRAY_FILTER_USE_KEY);
      $tests = new ArrayObjectWrapper($tests);
    }

    $commands = [];

    $provider = $this->getCommandProvider($style);
    foreach ($tests as $test => $details) {
      $commands[$test] = $provider->get($input, $output, $details);
    }

    if (empty($commands)) {
      throw new \Exception('No tests to run');
    }

    foreach ($commands as $test_name => $this_command) {
      $output->writeln('Running test: ' . $test_name);
      $ex = $this->getExecutor($this_command);
      if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
        $ex->outputCommand($output);
      }
      $ex->execute();
    }
  }

}
