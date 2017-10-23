<?php

namespace Project\Test\Testable\Command;

use Project\Command\ScriptCommand;
use Project\Configuration;
use Project\Test\Testable\Executor\NonExecutor;
use Project\Test\Testable\Provider\TestShellProvider;
use Symfony\Component\Console\Output\OutputInterface;

class TestScriptCommand extends ScriptCommand {

  protected function getCommandProvider($style = NULL) {
    $config = $this->getApplication()->config;
    $this->commandProvider = new TestShellProvider($config);
    return $this->commandProvider;
  }

  protected function getExecutor($command, OutputInterface $output = NULL) {
    if (!$output && $this->output) {
      $output = $this->output;
    }
    return new NonExecutor($command, $output);
  }
}
