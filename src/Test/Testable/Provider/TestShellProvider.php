<?php

namespace Project\Test\Testable\Provider;

use Project\Configuration;
use Project\Provider\ShellProvider;

class TestShellProvider extends ShellProvider {
  protected function validatePath($path, Configuration $config = NULL) {
    return $this->replacePathVariables($path, $config);
  }
}
