<?php

namespace Project\Test;

use Project\Test\Testable\Command\Config\TestExampleCommand;
use Project\Test\Testable\TestSingleDirectoryConfiguration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\StreamOutput;

class ConfigExampleTest extends TestCase {
  public function setUp() {
    $this->application = new Application();

    $this->application->config = new TestSingleDirectoryConfiguration(__DIR__ . '/fixtures/configuruation/example');
    $this->application->add(new TestExampleCommand());

    $this->application->getDefinition()->addOption(
      new InputOption(
        'environment',
        'e',
        InputOption::VALUE_OPTIONAL,
        'The environment to operate in.'
      )
    );

    $this->command = $this->application->find('config:example');
  }

  public function testExample() {
    $input = new ArrayInput(['example' => 'connect']);
    $output = new StreamOutput(fopen('php://memory', 'w', false));
    $this->command->run($input, $output);

    rewind($output->getStream());
    $display = stream_get_contents($output->getStream());
    $this->assertTrue(preg_match('/^connect:/', $display) === 1);
  }
}
