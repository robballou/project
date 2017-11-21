<?php

namespace Project\Test\Testable\Command;

use Project\Command\ScriptCommand;
use Project\Configuration;
use Project\Executor\OutputExecutor;
use Project\Test\Testable\Provider\TestShellProvider;
use Symfony\Component\Console\Output\OutputInterface;

class TestOutputExecutorCommand extends ScriptCommand {

  protected function getCommandProvider($style = NULL) {
    $config = $this->getApplication()->config;
    $this->commandProvider = new TestShellProvider($config);
    return $this->commandProvider;
  }
}
