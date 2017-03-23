<?php

namespace Project\Test\Testable\Executor;

use Project\Executor\Executor;

class NonExecutor extends Executor {
  public function execute($command = NULL) {
    if (!$command && $this->command) {
      $command = $this->command;
    }
    if ($this->output) {
      $this->output->writeln($command);
    }
    return $command;
  }

}
