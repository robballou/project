<?php

namespace Project\Test\Testable\Command\Drupal;

use Project\Command\Drupal\DrupalConsoleCommand;
use Project\Test\Testable\Executor\NonExecutor;
use Symfony\Component\Console\Output\OutputInterface;

class TestDrupalConsoleCommand extends DrupalConsoleCommand {
  protected function getExecutor($command, OutputInterface $output = NULL) {
    if (!$output && $this->output) {
      $output = $this->output;
    }
    return new NonExecutor($command, $output);
  }
}
