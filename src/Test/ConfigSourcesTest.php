<?php

namespace Project\Test;

use Project\Test\Testable\Command\Config\TestSourcesCommand;
use Project\Test\Testable\TestSingleDirectoryConfiguration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\StreamOutput;

class ConfigSourcesTest extends TestCase {
  public function setUp() {
    $this->application = new Application();

    $this->application->config = new TestSingleDirectoryConfiguration(__DIR__ . '/fixtures/configuration/example');
    $this->application->add(new TestSourcesCommand());

    $this->application->getDefinition()->addOption(
      new InputOption(
        'environment',
        'e',
        InputOption::VALUE_OPTIONAL,
        'The environment to operate in.'
      )
    );

    $this->command = $this->application->find('config:sources');
  }

  public function testSources() {
    $input = new ArrayInput([]);
    $output = new StreamOutput(fopen('php://memory', 'w', false));
    $this->command->run($input, $output);

    rewind($output->getStream());
    $display = stream_get_contents($output->getStream());
    $lines = explode("\n", $display);
    $this->assertTrue(count($lines) === 2);
    $this->assertTrue(preg_match('/config\.yml$/', $lines[0]) === 1);
  }
}
