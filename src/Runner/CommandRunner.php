<?php
namespace Project\Runner;

use Project\Runner\Runner;

class CommandRunner extends Runner {
  public function run() {
    $base = $this->config->getConfigOption('base');
    if (isset($this->thing->base)) {
      $base = $this->thing->base;
    }

    $command = [];
    if ($base) {
      $command[] = 'cd ' . $this->validatePath($base);
    }

    $command[] = $this->thing->command;
    $ex = $this->getExecutor(implode(' && ', $command));
    $ex->execute();
  }

  public function stop() {
    if (!$this->thing->get('stoppable', FALSE)) {
      return;
    }

    $base = $this->config->getConfigOption('base');
    if (isset($this->thing->base)) {
      $base = $this->thing->base;
    }

    $command = [];
    if ($base) {
      $command[] = 'cd ' . escapeshellarg($this->validatePath($base));
    }
    $command[] = $this->thing->get(['stop_command', 'command']);

    $ex = $this->getExecutor(implode(' && ', $command));
    $ex->execute();
  }

}