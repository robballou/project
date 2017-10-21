<?php

namespace Project\Test\Testable;

use Project\Configuration;

/**
 * Only include files from the starting directory.
 */
class TestSingleDirectoryConfiguration extends Configuration {
  public function getConfigFiles($current_directory = NULL) {
    $this->startingDirectory = $current_directory;

    $config_files = [];

    if (file_exists($this->startingDirectory . '/.project/config.local.yml')) {
      $config_files[] = $this->startingDirectory . '/.project/config.local.yml';
    }
    if (file_exists($this->startingDirectory . '/.project/config.yml')) {
      $config_files[] = $this->startingDirectory . '/.project/config.yml';
    }

    $this->files = $config_files;
    return $this->files;
  }
}
