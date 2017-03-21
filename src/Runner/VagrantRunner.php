<?php
namespace Project\Runner;

use Project\Runner\Runner;

class VagrantRunner extends Runner {
  public function run() {
    $vagrant_directory = $this->validatePath($this->thing->vagrant_directory);
    if (!$vagrant_directory) {
      throw new \Exception('No vagrant directory specified');
    }

    if (!is_dir($vagrant_directory)) {
      throw new \Exception('No vagrant directory is not a directory');
    }

    $ex = $this->getExecutor('cd ' . $vagrant_directory . ' && vagrant up');
    $ex->execute();
  }

  public function stop() {
    $vagrant_directory = $this->validatePath($this->thing->vagrant_directory);
    if (!$vagrant_directory) {
      throw new \Exception('No vagrant directory specified');
    }

    if (!is_dir($vagrant_directory)) {
      throw new \Exception('No vagrant directory is not a directory');
    }

    $ex = $this->getExecutor('cd ' . $vagrant_directory . ' && vagrant halt');
    $ex->execute();
  }

}
