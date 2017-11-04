<?php

namespace Project\Test;

namespace Project\Test;

use Project\Test\Testable\Command\TestTestCommand;
use Project\Test\Testable\TestSingleDirectoryConfiguration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\StreamOutput;

class TestTest extends TestCase {
  public function setUp() {
    $this->application = new Application();
    $this->application->config = new TestSingleDirectoryConfiguration(__DIR__ . '/fixtures/configuruation/example');
    $this->application->add(new TestTestCommand());

    $this->application->getDefinition()->addOption(
      new InputOption(
        'environment',
        'e',
        InputOption::VALUE_OPTIONAL,
        'The environment to operate in.'
      )
    );

    $this->command = $this->application->find('test');
  }

  /**
   * Test the test command with no defined tests.
   */
  public function testNoTests() {
    $input = new ArrayInput([]);
    $output = new StreamOutput(fopen('php://memory', 'w', false));

    $this->expectException(\Exception::class);
    $this->command->run($input, $output);

    // rewind($output->getStream());
    // $display = stream_get_contents($output->getStream());
    // var_dump($display);
    // $this->assertTrue(preg_match('/^connect:/', $display) === 1);
  }

  public function testWithTests() {
    $this->application->config->setConfig([
      'test' => [
        'default' => [
          'command' => 'phpunit',
        ],
      ],
    ]);
    $input = new ArrayInput([]);
    $output = new StreamOutput(fopen('php://memory', 'w', false));

    $this->command->run($input, $output);

    rewind($output->getStream());
    $display = stream_get_contents($output->getStream());
    $this->assertTrue(strpos($display, 'Running test: default') !== FALSE);
  }

  public function testWithMultipleTests() {
    $this->application->config->setConfig([
      'test' => [
        'default' => [
          'command' => 'phpunit',
        ],
        'another' => [
          'command' => 'phpunit',
        ],
      ],
    ]);
    $input = new ArrayInput([]);
    $output = new StreamOutput(fopen('php://memory', 'w', false));

    $this->command->run($input, $output);

    rewind($output->getStream());
    $display = stream_get_contents($output->getStream());
    $this->assertTrue(strpos($display, 'Running test: default') !== FALSE);
    $this->assertTrue(strpos($display, 'Running test: another') !== FALSE);
  }
  
  public function testWithMultipleTestsRunningOne() {
    $this->application->config->setConfig([
      'test' => [
        'default' => [
          'command' => 'phpunit',
        ],
        'another' => [
          'command' => 'phpunit',
        ],
      ],
    ]);
    $input = new ArrayInput(['test' => ['another']]);
    $output = new StreamOutput(fopen('php://memory', 'w', false));

    $this->command->run($input, $output);

    rewind($output->getStream());
    $display = stream_get_contents($output->getStream());
    $this->assertTrue(strpos($display, 'Running test: default') === FALSE);
    $this->assertTrue(strpos($display, 'Running test: another') !== FALSE);
  }
}
