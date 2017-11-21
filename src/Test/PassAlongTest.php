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
  }

  public function testDefault() {
    $this->application->add(new TestPassAlongCommand('myexample'));
    $command = $this->application->find('myexample');
    $commandTester = new CommandTester($command);
    $commandTester->execute(array(
      'command'  => 'myexample',
    ));

    // the output of the command in the console
    $output = trim($commandTester->getDisplay());
    $this->assertEquals("docker-compose exec 'web' myexample", $output);
  }
  
  public function testPassAlongWithCommand() {
    $this->application->add(new TestPassAlongCommand('myexample2'));
    $command = $this->application->find('myexample2');
    $commandTester = new CommandTester($command);
    $commandTester->execute(array(
      'command'  => 'myexample2',
    ));

    // the output of the command in the console
    $output = trim($commandTester->getDisplay());
    $this->assertEquals("docker-compose exec 'web' /bin/bash -c \"cd '/var/app' && ./bin/myexample\"", $output);
  }
  
  public function testPassAlongWithCommandWithoutBase() {
    $this->application->add(new TestPassAlongCommand('myexample3'));
    $command = $this->application->find('myexample3');
    $commandTester = new CommandTester($command);
    $commandTester->execute(array(
      'command'  => 'myexample3',
    ));

    // the output of the command in the console
    $output = trim($commandTester->getDisplay());
    $this->assertEquals("docker-compose exec 'web' /bin/bash -c \"python myexample\"", $output);
  }
  
  public function testPassAlongWithCommandAndArgsWithoutBase() {
    $this->application->add(new TestPassAlongCommand('myexample3'));
    $command = $this->application->find('myexample3');
    $commandTester = new CommandTester($command);
    $commandTester->execute(array(
      'command'  => 'myexample3',
      'thing',
    ));

    // the output of the command in the console
    $output = trim($commandTester->getDisplay());
    $this->assertEquals("docker-compose exec 'web' /bin/bash -c \"python myexample thing\"", $output);
  }
}
