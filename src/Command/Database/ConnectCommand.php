<?php

namespace Project\Command\Database;

use Project\Base\ProjectCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Project\Executor\Executor;

/**
 * Connect to the database.
 * 
 * Currently not setup.
 * 
 * @codeCoverageIgnore
 */
class ConnectCommand extends ProjectCommand {
  protected function configure() {
    $this
      // the name of the command (the part after "bin/console")
      ->setName('database:connect')
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    
  }

}
