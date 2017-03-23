<?php

namespace Project\Test;

use Project\Test\Testable\Command\TestConnectCommand;
use Project\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\StreamOutput;

class ConnectTest extends TestCase {
  public function setUp() {
    $this->application = new Application();
    $this->application->config = new Configuration(__DIR__ . '/fixtures/configuration/example');
    $this->application->add(new TestConnectCommand());

    $this->application->getDefinition()->addOption(
      new InputOption(
        'environment',
        'e',
        InputOption::VALUE_OPTIONAL,
        'The environment to operate in.'
      )
    );

    $this->command = $this->application->find('connect');
    // $this->commandTester = new CommandTester($this->command);
  }

  public function testDefault() {
    $input = new ArrayInput([]);
    $output = new StreamOutput(fopen('php://memory', 'w', false));
    $this->command->run($input, $output);

    rewind($output->getStream());
    $display = stream_get_contents($output->getStream());
    $this->assertEquals("docker-compose exec drupal /bin/bash", trim($display));
  }

}
