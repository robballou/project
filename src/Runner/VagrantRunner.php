<?php
namespace Project\Runner;

use Project\Runner\Runner;

class VagrantRunner extends Runner {
  /**
   * Start vagrant.
   */
  public function run() {
    $vagrant_directory = $this->validatePath($this->thing->get(['vagrant_directory', 'base']), $this->config);
    if (!$vagrant_directory) {
      throw new \Exception('No vagrant directory specified');
    }

    if (!is_dir($vagrant_directory)) {
      throw new \Exception('The vagrant directory is not a directory: ' . $vagrant_directory);
    }

    $ex = $this->getExecutor('cd ' . $vagrant_directory . ' && vagrant up');
    $ex->execute();
  }

  /**
   * Stop vagrant
   */
  public function stop() {
    $vagrant_directory = $this->validatePath($this->thing->get(['vagrant_directory', 'base']), $this->config);
    if (!$vagrant_directory) {
      throw new \Exception('No vagrant directory specified');
    }

    if (!is_dir($vagrant_directory)) {
      throw new \Exception('The vagrant directory is not a directory: ' . $vagrant_directory);
    }

    $ex = $this->getExecutor('cd ' . $vagrant_directory . ' && vagrant halt');
    $ex->execute();
  }

}
