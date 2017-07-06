<?php

namespace Project\Command\Drupal;

use Project\Base\ProjectCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Project\Executor\Executor;

class DrushCommand extends ProjectCommand {
  protected function configure() {
    $this
      // the name of the command (the part after "bin/console")
      ->setName('drush')
      // the short description shown while running "php bin/console list"
      ->setDescription('Run drush on the local site')
      ->addArgument('args', InputArgument::IS_ARRAY | InputArgument::OPTIONAL, 'Optional args to include')
      ->addOption('--output-command', 'Output the command before running it')
      ->ignoreValidationErrors()
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $config = $this->getApplication()->config;
    $command_config = $config->getCommandConfig('drush', $input);

    if (!$command_config) {
      $error = $output->getErrorOutput();
      $error->writeln('<error>This project is not configured to use drush.</error>');
      exit(1);
    }

    $environment = $config->getEnvironment($input);
    $style = $command_config->get([$environment . '.style', 'style']);

    if (!$style) {
      $error = $output->getErrorOutput();
      $error->writeln('<error>No drush "style" is set for this project.</error>');
      exit(1);
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
    switch ($style) {
      case 'alias':
      case 'drush-alias':
        $alias = $command_config->get([$environment . '.alias', 'alias']);
        $this_command = 'drush ';
        if ($alias) {
          if ($alias[0] != '@') {
            $alias = "@$alias";
          }
          $this_command .= $alias;
        }
        $this_command .= $options . $command;
        break;

      case 'terminus':
        $alias = $command_config->get([$environment . '.alias', 'alias']);
        $this_command = 'terminus drush ' . $alias . $options . $command;
        break;

      case 'docker-compose':
        $pre = '';
        if (isset($config->web_root)) {
          $pre = 'cd ' . $config->web_root . ' && ';
        }
        $container = $config->getConfigOption([
          'drush.' . $environment . '.container',
          'drush.container',
        ], 'drupal');
        $this_command = 'docker-compose exec ' . $container . ' /bin/bash -c "' . $pre . 'drush' . $options . $command . '"';
        break;

      case 'drocker':
        $pre = '';

        if (isset($config->web_root)) {
          $pre = 'cd ' . $config->web_root . ' && ';
        }
        elseif (isset($command_config->web_root)) {
          $pre = 'cd ' . $command_config->web_root . ' && ';
        }

        $this_command = $pre . 'drocker drush ' . $options . $command;
        if (isset($command_config->ssh)) {
          $this_command = 'ssh ' . $command_config->ssh . ' "' . addslashes($this_command) . '"';
        }
        break;
    }

    $ex = new Executor($this_command);
    if ($input->getOption('output-command') || $output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
      $ex->outputCommand($output);
    }
    $ex->execute();
  }

}
