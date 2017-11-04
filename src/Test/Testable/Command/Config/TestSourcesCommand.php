<?php

namespace Project\Test\Testable\Command\Config;

use Project\Command\Config\SourcesCommand;
use Project\Test\Testable\Executor\NonExecutor;
use Symfony\Component\Console\Output\OutputInterface;

class TestSourcesCommand extends SourcesCommand {
  protected function getExecutor($command, OutputInterface $output = NULL) {
    if (!$output && $this->output) {
      $output = $this->output;
    }
    return new NonExecutor($command, $output);
  }
}
