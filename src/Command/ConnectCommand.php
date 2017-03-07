<?php

namespace Project\Command;

use Project\Command\ProjectCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class ConnectCommand extends ProjectCommand {
  protected function configure() {
    $this
      // the name of the command (the part after "bin/console")
      ->setName('connect')
      // the short description shown while running "php bin/console list"
      ->setDescription('Connect via ssh/bash to the local site')
      ->addOption('environment', 'e', InputArgument::OPTIONAL, 'Optional environment specifier')
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $config = $this->getApplication()->config;
    $command_config = $this->getApplication()->config->getCommandConfig('drush', $input);
    $environment = $config->getEnvironment($input);

    $style = NULL;
    if ($command_config && isset($command_config->style)) {
      $style = $command_config->style;
    }
    elseif ($config && isset($config->local->$environment->style)) {
      $style = $config->local->$environment->style;
    }

    $this_command = '';
    switch ($style) {
      case 'docker-compose':
        $this_command = 'docker-compose exec drupal /bin/bash';
        break;
    }

    // better processing...
    $process = proc_open($this_command, array(0 => STDIN, 1 => STDOUT, 2 => STDERR), $pipes);
    $proc_status = proc_get_status($process);
    $exit_code = proc_close($process);
  }
}
