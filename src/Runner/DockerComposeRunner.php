<?php
namespace Project\Runner;

use Project\Runner\Runner;

/**
 * Runner class for docker-compose local setups
 */
class DockerComposeRunner extends Runner {
  public function run() {
    $base = isset($this->thing->base) ? $this->thing->base : NULL;
    if (!$base) {
      $base = $this->config->getProjectPath();
    }

    if (!$this->validatePath($base, $this->config)) {
      throw new \Exception('Could not find project base: ' . $base);
    }

    // check if the file option is set...
    $file = $this->thing->get('file', '');
    if ($file) {
      $file = '-f ' . escapeshellarg($file);
    }

    // build the command and check if we are running this in daemon mode (default)
    $cmd = 'cd ' . escapeshellarg($this->validatePath($base, $this->config)) . " && docker-compose $file up";
    if ($this->thing->get('daemon', TRUE)) {
      $cmd .= ' -d';
    }

    $ex = $this->getExecutor($cmd);
    $ex->execute();
  }

  public function stop() {
    $base = isset($this->thing->base) ? $this->thing->base : NULL;
    if (!$base) {
      $base = $this->config->getProjectPath();
    }

    if (!$this->validatePath($base, $this->config)) {
      throw new \Exception('Could not find project base: ' . $base);
    }

    $file = $this->thing->get('file', '');
    if ($file) {
      $file = '-f ' . escapeshellarg($file);
    }

    $ex = $this->getExecutor('cd ' . escapeshellarg($this->validatePath($base, $this->config)) . " && docker-compose $file down");
    $ex->execute();
  }

}
