<?php

namespace Project\Test\Testable\Command\Drupal;

use Project\Command\Drupal\DrushCommand;
use Project\Test\Testable\Executor\NonExecutor;
use Symfony\Component\Console\Output\OutputInterface;

class TestDrushCommand extends DrushCommand {
  protected function getExecutor($command, OutputInterface $output = NULL) {
    if (!$output && $this->output) {
      $output = $this->output;
    }
    return new NonExecutor($command, $output);
  }
}
