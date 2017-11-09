<?php

namespace Project\Test;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputOption;

/**
 * Abstract test case extensions for the entire project.
 */
abstract class ProjectTestCase extends TestCase {
  /**
   * Setup
   */
  public function setUp() {
    $this->application = new Application();
    $this->application->getDefinition()->addOption(
      new InputOption(
        'environment',
        'e',
        InputOption::VALUE_OPTIONAL,
        'The environment to operate in.'
      )
    );
  }

  /**
   * Get the contents of a stub fixture.
   * 
   * See 'fixtures/stubs' folder.
   *
   * @param string $stub
   * @return string
   * @throws Exception
   */
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
