<?php
namespace Project\Runner;

use Project\Runner\Runner;

class DockerComposeRunner extends Runner {
  public function run() {
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

    $ex = $this->getExecutor('cd ' . escapeshellarg($this->validatePath($base, $this->config)) . ' && docker-compose ' . $file . ' up -d');
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
