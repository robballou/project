<?php

namespace Project\Command;

use Project\Command\ExecutableCommand;
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
    $config = $this->getConfig();
    if (isset($config['drupal_console']['bin'])) {
      $command = $config['drupal_console']['bin'];
    }
    return $command;
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $config = $this->getCommandConfig('drupal_console');
    if (!$config) {
      $error = $output->getErrorOutput();
      $error->writeln('<error>This project is not configured to use drupal console.</error>');
      return;
    }

    if (!isset($config['style'])) {
      $error = $output->getErrorOutput();
      $error->writeln('<error>No drupal_console "style" is set for this project.</error>');
      return;
    }

    $command = '';
    if ($args = $input->getArgument('args')) {
      $command = ' ' . implode(' ', $args);
    }

    $options = '';
    if (isset($config['options'])) {
      foreach ($config['options'] as $option => $value) {
        $options .= '--' . $option . '=' . $value . ' ';
      }
    }
    if ($options) {
      $options = ' ' . $options;
    }

    switch ($config['style']) {
      case 'docker-compose':
        $pre = '';
        $project_config = $this->getConfig();
        if ($project_config && isset($project_config['web_root'])) {
          $pre = 'cd ' . $project_config['web_root'] . ' && ';
        }
        passthru('docker-compose exec drupal /bin/bash -c "' . $pre . $this->getExecutablePath() . $options . $command . '"');
        break;
    }

  }
}
