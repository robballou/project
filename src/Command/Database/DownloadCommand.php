<?php

namespace Project\Command\Database;

use Project\Base\ProjectCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Project\Executor\Executor;

/**
 * Download the database.
 * 
 * @codeCoverageIgnore
 */
class DownloadCommand extends ProjectCommand {

  /**
   * Configure.
   */
  protected function configure() {
    $this
      // the name of the command (the part after "bin/console")
      ->setName('database:download')
    ;
  }

  /**
   * Execute.
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $config = $this->getApplication()->config;
    $command_config = $config->getCommandConfig('database', $input);
    $environment = $config->getEnvironment($input);
    if (isset($command_config->$environment)) {
      throw new \Exception('Invalid environment: ' . $environment);
    }

    $style = $command_config->get($environment . '.style');
    if (!$style) {
      throw new \Exception('No style set for ' . $environment);
    }

    $this_command = '';
    switch ($style) {
      case 'docker-compose':
        $container = $command_config->get($environment . '.container', 'data');

        $this_command = 'docker-compose exec ' . $container;
        break;

    }

    $database_connection = $command_config->get($environment .'.type', 'mysql');
    $database = $command_config->get($environment .'.database', '');
    switch ($database_connection) {
      case 'mysql':
        $this_command .= ' mysqldump -u root -p atenmysql ' . $database;
        break;
    }

    var_dump($this_command);
  }

}
