<?php

namespace Project\Test;

use PHPUnit\Framework\TestCase;
use Project\Test\Testable\Command\TestCommand;

/**
 * Test commands
 */
class CommandTest extends TestCase {

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

}
