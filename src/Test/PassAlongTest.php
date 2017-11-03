<?php

namespace Project\Test;

use Project\Test\Testable\Command\TestPassAlongCommand;
use Project\Test\Testable\TestSingleDirectoryConfiguration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\StreamOutput;

class PassAlongTest extends TestCase {
  public function setUp() {
    $this->application = new Application();
    $this->application->config = new TestSingleDirectoryConfiguration(__DIR__ . '/fixtures/configuration/passalong');
    $this->application->add(new TestPassAlongCommand('myexample'));

    $this->application->getDefinition()->addOption(
      new InputOption(
        'environment',
        'e',
        InputOption::VALUE_OPTIONAL,
        'The environment to operate in.'
      )
    );

    $this->command = $this->application->find('myexample');
    // $this->commandTester = new CommandTester($this->command);
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
