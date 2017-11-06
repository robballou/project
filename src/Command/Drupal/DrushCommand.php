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
      throw new \Exception('This project is not configured to use drush.');
    }

    $environment = $config->getEnvironment($input);
    $style = $command_config->get([$environment . '.style', 'style']);

    if (!$style) {
      throw new \Exception('No drush "style" is set for this project.');
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
          $alias = ($alias[0] != '@') ? "@$alias" : $alias;
          $this_command .= $alias;
        }
        $this_command .= $options . $command;
        $style = 'shell';
        break;

      case 'terminus':
        $alias = $command_config->get([$environment . '.alias', 'alias']);
        $this_command = 'terminus drush ' . $alias . $options . $command;
        $style = 'shell';
        break;

      // case 'docker-compose':
      //   $pre = '';
      //   if (isset($config->web_root)) {
      //     $pre = 'cd ' . $config->web_root . ' && ';
      //   }
      //   $container = $config->getConfigOption([
      //     'drush.' . $environment . '.container',
      //     'drush.container',
      //   ], 'drupal');
      //   $this_command = 'docker-compose exec ' . $container . ' /bin/bash -c "' . $pre . 'drush' . $options . $command . '"';
      //   break;

      default:
        $this_command = 'drush ' . $options . $command;
        if ($root = $command_config->get('web_root')) {
          $this_command = 'cd ' . escapeshellarg($root) . ' && ' . $this_command;
        }
    }
    if ($this_command) {
      $command_config->script = $this_command;
    }
    
    $provider = $this->getCommandProvider($style);
    $this_command = $provider->get($input, $output, $command_config);

    $ex = $this->getExecutor($this_command, $output);
    if ($input->getOption('output-command') || $output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
      $ex->outputCommand($output);
    }
    $ex->execute();
  }

}
