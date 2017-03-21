<?php

namespace Project\Command\Drupal;

use Project\Base\ExecutableCommand;
use Project\Executor\Executor;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class DrupalConsoleCommand extends ExecutableCommand {
  protected function configure() {
    $this
      // the name of the command (the part after "bin/console")
      ->setName('drupal')
      // the short description shown while running "php bin/console list"
      ->setDescription('Run drupal console on the local site')
      ->addArgument('args', InputArgument::IS_ARRAY | InputArgument::OPTIONAL, 'Optional args to include')
      ->ignoreValidationErrors()
    ;
  }

  protected function getExecutablePath() {
    $command = 'drupal';
    $config = $this->getApplication()->config->getConfig();
    if (isset($config['drupal_console']['bin'])) {
      $command = $config['drupal_console']['bin'];
    }
    return $command;
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $config = $this->getApplication()->config;
    $environment = $config->getEnvironment($input);
    $command_config = $config->getCommandConfig('drupal_console', $input);

    if (!$command_config) {
      $error = $output->getErrorOutput();
      $error->writeln('<error>This project is not configured to use drupal console.</error>');
      return;
    }

    if (!isset($command_config->style)) {
      $error = $output->getErrorOutput();
      $error->writeln('<error>No drupal_console "style" is set for this project.</error>');
      return;
    }

    $command = '';
    if ($args = $input->getArgument('args')) {
      $command = ' ' . implode(' ', $args);
    }

    $options = '';
    if (isset($command_config->options)) {
      foreach ($command_config->options as $option => $value) {
        $options .= '--' . $option . '=' . $value . ' ';
      }
    }
    if ($options) {
      $options = ' ' . $options;
    }

    $this_command = '';
    switch ($command_config->style) {
      case 'docker-compose':
        $pre = '';
        if ($web_root = $config->getConfigOption('web_root')) {
          $pre = 'cd ' . escapeshellarg($web_root) . ' && ';
        }
        $container = $config->getConfigOption([
          'drush.' . $environment . '.container',
          'drush.container',
        ], 'drupal');

        $this_command = 'docker-compose exec ' . escapeshellarg($container) . ' /bin/bash -c "' . $pre . $this->getExecutablePath() . $options . $command . '"';
        break;
    }

    if (!$this_command) {
      throw new \Exception('Invalid drupal console "style": ' . $config->style);
    }

    $ex = new Executor($this_command);
    if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
      $ex->outputCommand($output);
    }
    $ex->execute();
  }

}
