<?php

namespace Project\Test\Testable\Command;

use Project\Command\ConnectCommand;
use Project\Test\Testable\Executor\NonExecutor;
use Symfony\Component\Console\Output\OutputInterface;

class TestConnectCommand extends ConnectCommand {
  protected function getExecutor($command, OutputInterface $output = NULL) {
    if (!$output && $this->output) {
      $output = $this->output;
    }
    return new NonExecutor($command, $output);
  }

}
