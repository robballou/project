<?php

namespace Project\Test\Testable\Command;

use Project\Command\PassAlongCommand;
use Project\Configuration;
use Project\Test\Testable\Executor\NonExecutor;
use Project\Test\Testable\Provider\TestShellProvider;
use Symfony\Component\Console\Output\OutputInterface;

class TestPassAlongCommand extends PassAlongCommand {

  protected function getExecutor($command, OutputInterface $output = NULL) {
    if (!$output && $this->output) {
      $output = $this->output;
    }
    return new NonExecutor($command, $output);
  }
}
