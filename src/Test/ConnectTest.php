<?php

namespace Project\Test;

use Project\Test\Testable\Command\TestConnectCommand;
use Project\Test\Testable\TestSingleDirectoryConfiguration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\StreamOutput;

class ConnectTest extends TestCase {
  public function setUp() {
    $this->application = new Application();
  }

  public function before($config) {
    $this->application->config = new TestSingleDirectoryConfiguration($config);
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
    $this->before(__DIR__ . '/fixtures/configuration/example');
    $input = new ArrayInput([]);
    $output = new StreamOutput(fopen('php://memory', 'w', false));
    $this->command->run($input, $output);

    rewind($output->getStream());
    $display = stream_get_contents($output->getStream());
    $this->assertEquals("docker-compose exec 'drupal' /bin/bash", trim($display));
  }

  public function testSSHWithHostUser() {
    $this->before(__DIR__ . '/fixtures/configuration/local-ssh');
    $input = new ArrayInput([]);
    $output = new StreamOutput(fopen('php://memory', 'w', false));
    $this->command->run($input, $output);

    rewind($output->getStream());
    $display = stream_get_contents($output->getStream());
    $this->assertEquals("ssh 'example@example.com' -t \"bash --login\"", trim($display));
  }

}
