<?php

namespace Project\Test;

use PHPUnit\Framework\TestCase;
use Project\Configuration;
use Project\Test\Testable\TestConfiguration;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;

class ConfigurationTest extends TestCase {
  public function testGetFilesFromChildDirectory() {
    $config = new Configuration(__DIR__ . '/fixtures/configuration/example');
    $files = $config->getConfigFiles();
    $this->assertTrue(is_array($files));
    $this->assertCount(2, $files);
    $this->assertTrue(boolval(preg_match('/configuration\/\.project\/config\.yml$/', $files[0])), "The parent config files should be loaded first");
    $this->assertTrue(boolval(preg_match('/configuration\/example\/\.project\/config\.yml$/', $files[1])), "The child config files should be loaded last");
  }

  public function testGetConfigFromChildDirectory() {
    $config = new Configuration(__DIR__ . '/fixtures/configuration/example');
    $this->assertEquals('vagrant', $config->local->style);
  }

  public function testGetCommandConfig() {
    $config = new Configuration(__DIR__ . '/fixtures/configuration/example');
    $connect = $config->getCommandConfig('connect');
    $this->assertTrue($connect !== NULL);
    $this->assertEquals('docker-compose', $connect->style);
  }

  /**
   * Test that command config includes command options when set...
   */
  public function testGetCommandConfigWithDefaults() {
    $config = new Configuration(__DIR__ . '/fixtures/configuration/example');
    $connect = $config->getCommandConfig('command');
    $this->assertTrue($connect !== NULL);
    $this->assertTrue(isset($connect->options, $connect->options->thing));

    $def = new InputDefinition();
    $def->addOption(new InputOption(
      'environment',
      'e',
      InputOption::VALUE_OPTIONAL,
      'The environment to operate in.'
    ));
    $input = new ArrayInput(['-e' => 'environment'], $def);

    $connect = $config->getCommandConfig('command', $input);
    $this->assertTrue($connect !== NULL);
    $this->assertTrue(isset($connect->options, $connect->options->thing));
  }

  /**
   * Test Configuration::getConfigOption()
   */
  public function testGetConfigOption() {
    $config = new Configuration(__DIR__ . '/fixtures/configuration/example');

    $tests = [
      [
        'input' => 'local.style',
        'expected' => 'vagrant',
      ],
      [
        'input' => ['local.style'],
        'expected' => 'vagrant',
      ],
      [
        'input' => ['local.thing', 'local.style'],
        'expected' => 'vagrant',
      ],
    ];

    foreach ($tests as $test) {
      $this->assertEquals($test['expected'], $config->getConfigOption($test['input']));
    }
  }

  public function testGetConfigOptionWithDefaults() {
    $config = new Configuration(__DIR__ . '/fixtures/configuration/example');

    $tests = [
      [
        'input' => 'local.dne',
        'default' => 123,
        'expected' => 123,
      ],
      [
        'input' => ['local.dne', 'local.dne2'],
        'default' => 123,
        'expected' => 123,
      ],
    ];

    foreach ($tests as $test) {
      $this->assertEquals($test['expected'], $config->getConfigOption($test['input'], $test['default']));
    }
  }

  /**
   * Test PathTrait::getPathVariables()
   */
  public function testGetPathVariablesWithBase() {
    $test_config = new TestConfiguration(__DIR__ . '/fixtures/configuration/example');
    $variables = $test_config->testGetPathVariables();
    $this->assertTrue(is_array($variables), "Variables should be an array");
    $this->assertTrue(isset($variables['PROJECT'], $variables['HOME']));
    $this->assertEquals('~/git/example', $variables['PROJECT']);
  }

  /**
   * Test PathTrait::getPathVariables()
   */
  public function testGetPathVariablesWithoutBase() {
    $test_config = new TestConfiguration(__DIR__ . '/fixtures/configuration');
    $variables = $test_config->testGetPathVariables();
    $this->assertTrue(is_array($variables), "Variables should be an array");
    $this->assertTrue(isset($variables['PROJECT']));
    $this->assertEquals(__DIR__ . '/fixtures/configuration', $variables['PROJECT']);
  }

  public function testReplacePathVariables() {
    $test_config = new TestConfiguration(__DIR__ . '/fixtures/configuration/example');
    $tests = [
      [
        'test' => '$PROJECT/project',
        'expect' => $test_config->getUserHome() . '/git/example/project',
      ],
    ];
    foreach ($tests as $test) {
      $this->assertEquals($test['expect'], $test_config->testReplacePathVariables($test['test'], $test_config));
    }
  }

}
