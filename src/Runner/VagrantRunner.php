<?php

namespace Project\Runner;

use Project\Runner\Runner;

/**
 * Handles running and stopping vagrant local environments.
 */
class VagrantRunner extends Runner {

  /**
   * Get the vagrant directory for this local.
   */
  protected function getVagrantDirectory() {
    $vagrant_directory = $this->validatePath($this->thing->get(['vagrant_directory', 'base']), $this->config);
    if (!$vagrant_directory) {
      throw new \Exception('No vagrant directory specified');
    }

    if (!is_dir($vagrant_directory)) {
      throw new \Exception('The vagrant directory is not a directory: ' . $vagrant_directory);
    }
    return $vagrant_directory;
  }

  /**
   * Start vagrant.
   */
  public function run() {
    $vagrant_directory = $this->getVagrantDirectory();
    $ex = $this->getExecutor('cd ' . escapeshellarg($vagrant_directory) . ' && vagrant up');
    $ex->execute();
  }

  /**
   * Stop vagrant
   */
  public function stop() {
    $vagrant_directory = $this->getVagrantDirectory();
    $ex = $this->getExecutor('cd ' . escapeshellarg($vagrant_directory) . ' && vagrant halt');
    $ex->execute();
  }

}
