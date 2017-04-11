<?php
namespace Project\Runner;

use Project\Runner\Runner;

class ScriptRunner extends Runner {
  public function run() {
    if (!isset($this->thing->script)) {
      throw new \Exception('No "script" set for this component');
    }
    $base = $this->config->getConfigOption('base');
    if (isset($this->thing->base)) {
      $base = $this->thing->base;
    }

    $command = [];
    $path = ($base) ? $this->validatePath($base) : FALSE;
    if ($path) {
      $command[] = 'cd ' . $path;
    }
    if ($base && !$path) {
      throw new \Exception('Could not validate path: ' . $base);
    }

    $script = $this->validatePath($this->thing->script, $this->config);
    if (!$script) {
      $script = $this->thing->script;
    }
    $command[] = $script;
    $ex = $this->getExecutor(implode(' && ', $command));
    $ex->execute();
  }

  public function stop() {
    if (!$this->thing->get('stoppable', FALSE)) {
      $this->outputVerbose('Cannot stop this script. If this script can be stopped, add the "stoppable" flag in your project config');
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
    $command[] = $this->thing->get(['stop_script', 'script']);

    $ex = $this->getExecutor(implode(' && ', $command));
    $ex->execute();
  }

}
