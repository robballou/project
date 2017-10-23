<?php

namespace Project\Test;

use Project\Test\Testable\Command\TestScriptCommand;
use Project\Test\Testable\TestSingleDirectoryConfiguration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\StreamOutput;

class ScriptTest extends TestCase {
  public function setUp() {
    $this->application = new Application();
  }

  public function before($config) {
    $this->application->config = new TestSingleDirectoryConfiguration($config);
    $this->application->add(new TestScriptCommand());

    $this->application->getDefinition()->addOption(
      new InputOption(
        'environment',
        'e',
        InputOption::VALUE_OPTIONAL,
        'The environment to operate in.'
      )
    );

    $this->command = $this->application->find('script');
    // $this->commandTester = new CommandTester($this->command);
  }

  public function testDefault() {
    $this->before(__DIR__ . '/fixtures/configuration/example');
    $input = new ArrayInput(['name' => 'run']);
    $output = new StreamOutput(fopen('php://memory', 'w', false));
    $this->command->run($input, $output);

    rewind($output->getStream());
    $display = stream_get_contents($output->getStream());
    $this->assertEquals("./run.sh", trim($display));
  }

  public function testWithBasedir() {
    $this->before(__DIR__ . '/fixtures/configuration/example');
    $input = new ArrayInput(['name' => 'dir']);
    $output = new StreamOutput(fopen('php://memory', 'w', false));
    $this->command->run($input, $output);

    rewind($output->getStream());
    $display = stream_get_contents($output->getStream());

    // the command should be like: cd '/some/path/example/scripts' && ./dir.sh
    $this->assertTrue(preg_match('/^cd \'.+example\/scripts\' && \.\/dir\.sh/', trim($display)) === 1);
  }

}
