<?php

namespace Project\Test;

use Project\Test\Testable\Command\Build\TestBuildCommand;
use Project\Test\Testable\TestSingleDirectoryConfiguration;
use Project\Test\ProjectTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\StreamOutput;

class BuildTest extends ProjectTestCase {
  public function setUp() {
    $this->application = new Application();
    $this->application->config = new TestSingleDirectoryConfiguration(__DIR__ . '/fixtures/configuration/example');
    $this->application->add(new TestBuildCommand());

    $this->application->getDefinition()->addOption(
      new InputOption(
        'environment',
        'e',
        InputOption::VALUE_OPTIONAL,
        'The environment to operate in.'
      )
    );

    $this->command = $this->application->find('build');
    // $this->commandTester = new CommandTester($this->command);
  }

  public function testNoBuilds() {
    $this->expectException(\Exception::class);
    $input = new ArrayInput([]);
    $output = new StreamOutput(fopen('php://memory', 'w', false));
    $this->command->run($input, $output);
  }

  public function testBuilds() {
    $this->application->config->setConfigYaml($this->getStub('builds_default'));
    $input = new ArrayInput(['thing' => ['js']]);
    $output = new StreamOutput(fopen('php://memory', 'w', false));
    $this->command->run($input, $output);

    rewind($output->getStream());
    $display = stream_get_contents($output->getStream());
    $this->assertTrue(strpos($display, 'webpack') !== FALSE);
  }
}
