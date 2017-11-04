<?php

namespace Project\Test\Testable;

use Project\Configuration;

/**
 * Only include files from the starting directory.
 */
class TestSingleDirectoryConfiguration extends Configuration {
  public function getConfigFiles($current_directory = NULL) {
    if ($this->files) {
      return $this->files;
    }

    if ($current_directory) {
      $this->startingDirectory = $current_directory;
    }

    $config_files = [];

    if (file_exists($this->startingDirectory . DIRECTORY_SEPARATOR . '.project/config.local.yml')) {
      $config_files[] = $this->startingDirectory . DIRECTORY_SEPARATOR . '.project/config.local.yml';
    }
    if (file_exists($this->startingDirectory . DIRECTORY_SEPARATOR . '.project/config.yml')) {
      $config_files[] = $this->startingDirectory . DIRECTORY_SEPARATOR . '.project/config.yml';
    }
    
    $this->files = $config_files;
    return $this->files;
  }
}
