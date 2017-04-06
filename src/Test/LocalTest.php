<?php

namespace Project\Test;

use Project\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\StreamOutput;

use Project\Test\Testable\Local\TestRunCommand;

class LocalTest extends TestCase {
  public function setUp() {
    $this->application = new Application();
  }

  /**
   * Test that settings from local.default get picked up
   */
  public function testDefaultLocal() {
    $this->application->config = new Configuration(__DIR__ . '/fixtures/configuration/local');
    $this->application->add(new TestRunCommand());
    $command = $this->application->find('local:run');

    $input = new ArrayInput([]);
    $output = new StreamOutput(fopen('php://memory', 'w', FALSE));

    $command->run($input, $output);
    rewind($output->getStream());
    $display = stream_get_contents($output->getStream());
    $this->assertEquals('run vagrant', trim($display));
  }

  /**
   * Test that settings from local.components.default get picked up
   */
  public function testLocalDefault() {
    $this->application->config = new Configuration(__DIR__ . '/fixtures/configuration/local2');
    $this->application->add(new TestRunCommand());
    $command = $this->application->find('local:run');

    $input = new ArrayInput(['thing' => ['default']]);
    $output = new StreamOutput(fopen('php://memory', 'w', FALSE));

    $command->run($input, $output);
    rewind($output->getStream());
    $display = stream_get_contents($output->getStream());
    $this->assertEquals('run vagrant', trim($display));
  }

  /**
   * Test that we get the things we expect when just running 'local:run'.
   */
  public function testResolveThingsDefaultWithLocalConfig() {
    $tests = [
      [
        'input' => [],
        'expect' => ['default' => ['style' => 'vagrant']],
      ],
      [
        'input' => ['default'],
        'expect' => ['default' => ['style' => 'vagrant']],
      ],
    ];

    $this->application->config = new Configuration(__DIR__ . '/fixtures/configuration/local');
    $this->application->add(new TestRunCommand());
    $command = $this->application->find('local:run');

    foreach ($tests as $test) {
      $input = new ArrayInput(['thing' => $test['input']]);
      $output = new StreamOutput(fopen('php://memory', 'w', FALSE));
      $things = $command->testableResolveThings($input, $output);
      rewind($output->getStream());
      $display = stream_get_contents($output->getStream());

      foreach ($test['expect'] as $key => $value) {
        $this->assertTrue(isset($things->$key), "'$key' should be part of the things we are running. Found: " . implode(', ', $things->getKeys()));
        foreach ($value as $value_key => $value_value) {
          $this->assertTrue(isset($things->$key->$value_key), "'$value_key' should be set within '$key'");
          $this->assertEquals($value_value, $things->$key->$value_key, "'thing->$key->$value_key' does not equal the expected value");
        }
      }
    }
  }

  /**
   * Test that we get the things we expect when just running 'local:run'.
   */
  public function testResolveThingsDefaultWithLocal2Config() {
    $tests = [
      [
        'input' => [],
        'expect' => ['default' => ['style' => 'vagrant'], 'other' => ['style' => 'docker-compose']],
      ],
      [
        'input' => ['default'],
        'expect' => ['default' => ['style' => 'vagrant']],
      ],
    ];

    $this->application->config = new Configuration(__DIR__ . '/fixtures/configuration/local2');
    $this->application->add(new TestRunCommand());
    $command = $this->application->find('local:run');

    foreach ($tests as $test) {
      $input = new ArrayInput(['thing' => $test['input']]);
      $output = new StreamOutput(fopen('php://memory', 'w', FALSE));
      $things = $command->testableResolveThings($input, $output);
      rewind($output->getStream());
      $display = stream_get_contents($output->getStream());

      foreach ($test['expect'] as $key => $value) {
        $this->assertTrue(isset($things->$key), "'$key' should be part of the things we are running. Found: " . implode(', ', $things->getKeys()));
        foreach ($value as $value_key => $value_value) {
          $this->assertTrue(isset($things->$key->$value_key), "'$value_key' should be set within '$key'");
          $this->assertEquals($value_value, $things->$key->$value_key, "'thing->$key->$value_key' does not equal the expected value");
        }
      }
    }
  }

}
