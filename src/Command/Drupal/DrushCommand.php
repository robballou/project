<?php

namespace Project\Command\Drupal;

use Project\Command\ProjectCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class DrushCommand extends ProjectCommand {
  protected function configure() {
    $this
      // the name of the command (the part after "bin/console")
      ->setName('drush')
      // the short description shown while running "php bin/console list"
      ->setDescription('Run drush on the local site')
      ->addOption('environment', 'e', InputArgument::OPTIONAL, 'Optional environment specifier')
      ->addArgument('args', InputArgument::IS_ARRAY | InputArgument::OPTIONAL, 'Optional args to include')
      ->ignoreValidationErrors()
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $config = $this->getApplication()->config->getConfig();
    $command_config = $this->getApplication()->config->getCommandConfig('drush', $input);
    if (!$command_config) {
      $error = $output->getErrorOutput();
      $error->writeln('<error>This project is not configured to use drush.</error>');
      return;
    }

    if (!isset($command_config['style'])) {
      $error = $output->getErrorOutput();
      $error->writeln('<error>No drush "style" is set for this project.</error>');
      return;
    }

    $command = '';
    if ($args = $input->getArgument('args')) {
      $command = ' ' . implode(' ', $args);
    }

    $options = '';
    if (isset($command_config['options'])) {
      foreach ($command_config['options'] as $option => $value) {
        $options .= '--' . $option . '=' . $value . ' ';
      }
    }
    if ($options) {
      $options = ' ' . $options;
    }

    $this_command = '';
    switch ($command_config['style']) {
      case 'alias':
        $this_command = 'drush @' . $command_config['alias'] . $options . $command;
        break;

      case 'docker-compose':
        $pre = '';
        if (isset($config['web_root'])) {
          $pre = 'cd ' . $config['web_root'] . ' && ';
        }
        $this_command = 'docker-compose exec drupal /bin/bash -c "' . $pre . 'drush' . $options . $command . '"';
        break;
    }

    passthru($this_command);
  }
}
