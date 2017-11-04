<?php

namespace Project\Test\Testable\Command\Config;

use Project\Command\Config\ExampleCommand;
use Project\Test\Testable\Executor\NonExecutor;
use Symfony\Component\Console\Output\OutputInterface;

class TestExampleCommand extends ExampleCommand {
  protected function getExecutor($command, OutputInterface $output = NULL) {
    if (!$output && $this->output) {
      $output = $this->output;
    }
    return new NonExecutor($command, $output);
  }
}
