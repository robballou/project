<?php

namespace Project\Test\Testable\Command\Deploy;

use Project\Command\Deploy\EnvCommand;
use Project\Configuration;
use Project\Test\Testable\Executor\NonExecutor;
use Project\Test\Testable\Provider\TestShellProvider;
use Symfony\Component\Console\Output\OutputInterface;

class TestEnvCommand extends EnvCommand {

  protected function getExecutor($command, OutputInterface $output = null) {
    if (!$output && $this->output) {
      $output = $this->output;
    }
    return new NonExecutor($command, $output);
  }
}
