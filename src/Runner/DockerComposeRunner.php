<?php
namespace Project\Runner;

use Project\Runner\Runner;

class DockerComposeRunner extends Runner {
  public function run() {
    $base = isset($this->thing->base) ? $this->thing->base : NULL;
    if (!$base) {
      $base = $this->config->getProjectPath();
    }

    $ex = $this->getExecutor('cd ' . $this->validatePath($base) . ' && docker-compose up -d');
    $ex->execute();
  }

  public function stop() {
    $base = isset($this->thing->base) ? $this->thing->base : NULL;
    if (!$base) {
      $base = $this->config->getProjectPath();
    }

    $ex = $this->getExecutor('cd ' . $this->validatePath($base) . ' && docker-compose down');
    $ex->execute();
  }

}
