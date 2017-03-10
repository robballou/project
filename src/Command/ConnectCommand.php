<?php

namespace Project\Command;

use Project\Base\ProjectCommand;
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
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $config = $this->getApplication()->config;
    $environment = $config->getEnvironment($input);

    // get the first one of the following options to figure out the style of
    // connection...
    $style = $config->getConfigOption([
      'connect.' . $environment . '.style',
      'connect.style',
      'local.' . $environment . '.style',
      'local.style',
    ]);

    $this_command = '';
    switch ($style) {
      case 'vagrant':
        // we need the vagrant directory...
        $vagrant_directory = $config->getConfigOption([
          'connect.' . $environment . '.vagrant_directory',
          'connect.vagrant_directory',
          'local.' . $environment . '.vagrant_directory',
          'local.vagrant_directory',
        ]);

        $this_command = 'cd ' . $vagrant_directory . ' && vagrant ssh';
        break;

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
