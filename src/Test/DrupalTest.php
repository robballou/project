<?php

namespace Project\Test;

use Project\Test\Testable\Command\Drupal\TestDrushCommand;
use Project\Test\Testable\Command\Drupal\TestDrupalConsoleCommand;
use Project\Test\Testable\TestSingleDirectoryConfiguration;
use Project\Test\ProjectTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\StreamOutput;

class DrupalTest extends ProjectTestCase {
  public function setUp() {
    parent::setUp();
    $this->application->config = new TestSingleDirectoryConfiguration(__DIR__ . '/fixtures/configuration/drupal');
    $this->application->add(new TestDrupalConsoleCommand());
    $this->application->add(new TestDrushCommand());

    $this->drupal = $this->application->find('drupal');
    $this->drush = $this->application->find('drush');
  }

  protected function getInput(array $args) {
    $input = new ArrayInput($args, $this->application->getDefinition());
    return $input;
  }

  public function testDrush() {
    $input = new ArrayInput(['args' => ['uli']]);
    $output = new StreamOutput(fopen('php://memory', 'w', false));
    $this->drush->run($input, $output);

    rewind($output->getStream());
    $display = trim(stream_get_contents($output->getStream()));
    $this->assertEquals('./drush @example.dev uli', $display);
  }

  public function testDrushWithTerminus() {
    $command_tester = new CommandTester($this->drush);
    $command_tester->execute(
      [
        'command' => 'drush',
        'args' => ['uli'],
        '--environment' => 'terminus',
      ]
    );
    $output = trim($command_tester->getDisplay());
    $this->assertEquals('./terminus drush example.dev uli', $output);
  }

  public function testDrushWithDockerComposer() {
    $command_tester = new CommandTester($this->drush);
    $command_tester->execute(
      [
        'command' => 'drush',
        'args' => ['uli'],
        '--environment' => 'dc',
      ]
    );
    $output = trim($command_tester->getDisplay());
    $this->assertEquals("docker-compose exec 'web' /bin/bash -c \"drush  uli\"", $output);
  }

  public function testDrushWithDockerComposerAndWebRoot() {
    $command_tester = new CommandTester($this->drush);
    $command_tester->execute(
      [
        'command' => 'drush',
        'args' => ['uli'],
        '--environment' => 'dc2',
      ]
    );
    $output = trim($command_tester->getDisplay());
    $this->assertEquals("docker-compose exec 'web' /bin/bash -c \"cd '/var/app' && drush  uli\"", $output);
  }

  public function testDrushWithOptions() {
    $this->markTestIncomplete(
      'This test has not been implemented yet.'
    );
  }
}
