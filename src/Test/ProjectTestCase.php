<?php

namespace Project\Test;

use PHPUnit\Framework\TestCase;

abstract class ProjectTestCase extends TestCase {
  protected function getStub($stub) {
    if (!preg_match('/^[a-zA-Z0-9-_]+$/', $stub)) {
      throw new \Exception('Invalid stub: ' . $stub);
    }
    $stub_path = __DIR__ . '/fixtures/stubs/' . $stub . '.yml';
    if (file_exists($stub_path)) {
      return file_get_contents($stub_path);
    }

    throw new \Exception('Could not find stub: ' . $stub);
  }
}
