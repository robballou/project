<?php

namespace Project\Test;

use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Input\InputOption;
use Project\Test\ProjectTestCase;
use Project\Test\Testable\Command\TestCommand;
use Project\Test\Testable\Command\TestOutputExecutorCommand;
use Project\Test\Testable\TestSingleDirectoryConfiguration;

/**
 * Test basic command functionality.
 * 
 * E.g. ProjectCommand...
 */
class CommandTest extends ProjectTestCase {

  /**
   * Test ProjectCommand::isNonAssociativeArray().
   */
  public function testIsNonAssociativeArray() {
    $tests = [
      [
        'input' => [1, 2, 3],
        'expected' => TRUE,
      ],
      [
        'input' => [0 => 1, 1 => 2, 2 => 3],
        'expected' => TRUE,
      ],
      [
        'input' => [10 => 1, 20 => 2, 30 => 3],
        'expected' => TRUE,
      ],
      [
        'input' => ['thing' => 1, 1 => 2, 2 => 3],
        'expected' => FALSE,
      ],
    ];

    $test_count = 1;
    $command = new TestCommand();
    foreach ($tests as $test) {
      $this->assertEquals($test['expected'], $command->testIsNonAssocativeArray($test['input']), "Test $test_count failed");
      $test_count++;
    }
  }

  public function testNoExecute() {
    $this->application->getDefinition()->addOption(
      new InputOption(
        'no-execute',
        'x',
        InputOption::VALUE_OPTIONAL,
        'Output the command but do not run it'
      )
    );
    $this->application->config = new TestSingleDirectoryConfiguration(__DIR__ . '/fixtures/configuration/example');

    $command = new TestOutputExecutorCommand('test');
    $this->application->add($command);
    $command_tester = new CommandTester($command);
    $command_tester->execute(array(
      'name' => 'run',
      'command'  => 'myexample3',
      '--no-execute' => true,
    ));
    $output = trim($command_tester->getDisplay());
    $this->assertEquals("/bin/zsh -lc \"./run.sh\"", $output);
  }

}
