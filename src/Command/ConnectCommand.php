<?php

namespace Project\Command;

use Project\Base\ProjectCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Project\Executor\Executor;

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
      case 'ssh':
        $host = $config->getConfigOption([
          'connect.' . $environment . '.host',
          'connect.host',
          'local.' . $environment . '.host',
          'local.host',
        ]);
        if (!$host) {
          throw new \Exception('No host found for this environment');
        }

        $user = $config->getConfigOption([
          'connect.' . $environment . '.user',
          'connect.user',
          'local.' . $environment . '.user',
          'local.user',
        ]);
        if ($user) {
          $host = $user . '@' . $host;
        }

        $sub_command = 'bash --login';
        $base = $config->getConfigOption(['connect.' . $environment . '.base',
          'connect.base',
          'local.' . $environment . '.base',
          'local.base',
        ]);
        if ($base) {
          $sub_command = 'cd ' . $base . '; ' . $sub_command;
        }
        $this_command = 'ssh ' . $host . ' -t "' . $sub_command . '"';
        break;

      case 'vagrant':
        // we need the vagrant directory...
        $vagrant_directory = $config->getConfigOption([
          'connect.' . $environment . '.vagrant_directory',
          'connect.' . $environment . '.base',
          'connect.vagrant_directory',
          'connect.base',
          'local.' . $environment . '.vagrant_directory',
          'local.' . $environment . '.base',
          'local.default.vagrant_directory',
          'local.default.base',
          'local.vagrant_directory',
          'local.base',
        ]);

        $vagrant_directory = $this->validatePath($vagrant_directory, $config);
        if (!$vagrant_directory) {
          throw new \Exception('Could not find vagrant directory');
        }

        $this_command = 'cd ' . escapeshellarg($vagrant_directory) . ' && vagrant ssh';
        break;

      case 'docker-compose':
        $container = $config->getConfigOption([
          'connect.' . $environment . '.container',
          'connect.container',
          'local.' . $environment . '.container',
          'local.container',
        ]);
        if (!$container) {
          throw new \Exception('No container is set for this environment');
        }
        $this_command = 'docker-compose exec ' . $container . ' /bin/bash';
        break;

      case 'drocker':
        $base = $config->getConfigOption([
          'connect.' . $environment . '.base',
          'connect.default.base',
          'local.' . $environment . '.base',
          'local.base',
        ]);

        $base = $this->validatePath($base, $config);
        if (!$base) {
          throw new \Exception('Could not find base directory');
        }

        $this_command = 'cd ' . escapeshellarg($base) . ' && drocker connect';
        break;
    }

    // better processing...
    $ex = $this->getExecutor($this_command);
    $ex->execute();
  }

}
