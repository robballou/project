<?php

namespace Project\Test;

use Project\Configuration;
use Project\Test\ProjectTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\StreamOutput;

use Project\Test\Testable\Command\Local\TestRunCommand;

class LocalTest extends ProjectTestCase {
  public function setUp() {
    $this->application = new Application();
    $this->application->getDefinition()->addOption(
      new InputOption(
        'environment',
        'e',
        InputOption::VALUE_OPTIONAL,
        'The environment to operate in.'
      )
    );
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
    $this->assertEquals('vagrant up', trim($display));
  }

  /**
   * Test that settings from local.default get picked up
   */
  public function testDefaultLocal() {
    $this->application->config = new Configuration(__DIR__ . '/fixtures/configuration/local');
    $this->application->add(new TestRunCommand());
    $command = $this->application->find('local:stop');

    $input = new ArrayInput([]);
    $output = new StreamOutput(fopen('php://memory', 'w', FALSE));

    $command->run($input, $output);
    rewind($output->getStream());
    $display = stream_get_contents($output->getStream());
    $this->assertEquals('vagrant halt', trim($display));
  }

  /**
   * Test that settings from local.components.default get picked up
   */
  public function testDefaultLocal2() {
    $this->application->config = new Configuration(__DIR__ . '/fixtures/configuration/local2');
    $this->application->add(new TestRunCommand());
    $command = $this->application->find('local:run');

    $input = new ArrayInput(['thing' => ['default']]);
    $output = new StreamOutput(fopen('php://memory', 'w', FALSE));

    $command->run($input, $output);
    rewind($output->getStream());
    $display = stream_get_contents($output->getStream());
    $this->assertEquals('vagrant up', trim($display));
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
        'all' => TRUE,
        'expect' => ['default' => ['style' => 'vagrant'], 'other' => ['style' => 'docker-compose'], 'image' => ['style' => 'docker']],
      ],
      [
        'input' => [],
        'all' => FALSE,
        'expect' => ['default' => ['style' => 'vagrant']],
      ],
      [
        'input' => ['default'],
        'expect' => ['default' => ['style' => 'vagrant']],
      ],
    ];

    $this->application->config = new Configuration(__DIR__ . '/fixtures/configuration/local2');
    $this->application->add(new TestRunCommand());
    $command = $this->application->find('local:run');

    for ($i = 0; $i < count($tests); $i++) {
      $test = $tests[$i];
      $input_array = ['thing' => $test['input']];
      if (isset($test['all']) && $test['all']) {
        $input_array['--all'] = $test['all'];
      }
      $input = new ArrayInput($input_array);
      $output = new StreamOutput(fopen('php://memory', 'w', FALSE));
      $things = $command->testableResolveThings($input, $output);
      rewind($output->getStream());

      $display = stream_get_contents($output->getStream());

      $this->assertTrue($things !== NULL, "Test $i \$things is NULL");
      foreach ($test['expect'] as $key => $value) {
        $this->assertTrue(isset($things->$key), "Test $i: '$key' should be part of the things we are running. Found: " . implode(', ', $things->getKeys()));
        foreach ($value as $value_key => $value_value) {
          $this->assertTrue(isset($things->$key->$value_key), "Test $i: '$value_key' should be set within '$key'");
          $this->assertEquals($value_value, $things->$key->$value_key, "Test $i: 'thing->$key->$value_key' does not equal the expected value");
        }
      }

      $expected_count = count(array_keys($test['expect']));
      $actual_count = count($things->getKeys());
      $this->assertTrue($actual_count == $expected_count, "Test $i expected keys: " . $expected_count . "; Found: " . $actual_count);
    }
  }

  public function testPre() {
    $this->application->config = new Configuration();
    $this->application->config->setConfigYaml($this->getStub('local_pre'));
    $this->application->add(new TestRunCommand());
    $command = $this->application->find('local:run');
    $input = new ArrayInput([]);
    $output = new StreamOutput(fopen('php://memory', 'w', FALSE));

    $command->run($input, $output);
    rewind($output->getStream());
    $display = stream_get_contents($output->getStream());
    $lines = explode("\n", $display);
    $this->assertEquals('date', $lines[0]);
  }

  public function testPost() {
    $this->application->config = new Configuration();
    $this->application->config->setConfigYaml($this->getStub('local_post'));
    $this->application->add(new TestRunCommand());
    
    $command = $this->application->find('local:run');

    $this->assertEquals('Project\Provider\ShellProvider', $this->application->config->getProviderClass('shell'));

    $input = new ArrayInput([]);
    $output = new StreamOutput(fopen('php://memory', 'w', FALSE));

    $command->run($input, $output);
    rewind($output->getStream());
    $display = stream_get_contents($output->getStream());
    $lines = explode("\n", $display);
    $this->assertEquals('date', $lines[1]);
  }

}
