<?php

namespace Project\Test;

use Project\Test\Testable\Command\TestPassAlongCommand;
use Project\Test\Testable\TestSingleDirectoryConfiguration;
use Project\Test\ProjectTestCase;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\StreamOutput;

class PassAlongTest extends ProjectTestCase {
  public function setUp() {
    parent::setUp();

    $this->application->config = new TestSingleDirectoryConfiguration(__DIR__ . '/fixtures/configuration/passalong');
    $this->application->add(new TestPassAlongCommand('myexample'));
    $this->command = $this->application->find('myexample');
  }

  public function testDefault() {
    $commandTester = new CommandTester($this->command);
    $commandTester->execute(array(
      'command'  => 'myexample',
    ));

    // the output of the command in the console
    $output = trim($commandTester->getDisplay());
    $this->assertEquals("docker-compose exec 'web' myexample", $output);
  }
}
