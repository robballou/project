<?php
namespace Project\Runner;

use Project\Runner\Runner;

class ScriptRunner extends Runner {
  public function run() {
    $base = $this->config->getConfigOption('base');
    if (isset($this->thing->base)) {
      $base = $this->thing->base;
    }

    $command = [];
    if ($base) {
      $command[] = 'cd ' . $this->validatePath($base);
    }

    $script = $this->validatePath($this->thing->script, $this->config);
    if (!$script) {
      throw new \Exception('Cannot find script: '. $this->thing->script);
    }
    $command[] = $script;
    $ex = $this->getExecutor(implode(' && ', $command));
    $ex->execute();
  }

  public function stop() {
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

}
