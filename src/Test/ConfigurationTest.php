<?php

namespace Project\Test;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;

use Project\Configuration;
use Project\Test\Testable\TestConfiguration;


class ConfigurationTest extends TestCase {
  public function testGetFilesFromChildDirectory() {
    $config = new Configuration(__DIR__ . '/fixtures/configuration/example');
    $files = $config->getConfigFiles();

    // strip out the project's own .project config file 😁
    $config_files = [];
    array_map(function ($file) use (&$config_files) {
      if (strpos($file, 'fixtures') !== FALSE) {
        $config_files[] = $file;
      }
    }, $files);

    $this->assertTrue(is_array($config_files));
    $this->assertCount(2, $config_files);
    $this->assertTrue(boolval(preg_match('/configuration\/\.project\/config\.yml$/', $config_files[0])), "The parent config files should be loaded first");
    $this->assertTrue(boolval(preg_match('/configuration\/example\/\.project\/config\.yml$/', $config_files[1])), "The child config files should be loaded last");
  }

  public function testGetConfigFromChildDirectory() {
    $config = new Configuration(__DIR__ . '/fixtures/configuration/example');
    $this->assertEquals('vagrant', $config->local->style);
  }

  public function testGetFilesFromChildDirectoryWithLocalFile() {
    $config = new Configuration(__DIR__ . '/fixtures/configuration/withlocal');
    $files = $config->getConfigFiles();

    // strip out the project's own .project config file 😁
    $config_files = [];
    array_map(function ($file) use (&$config_files) {
      if (strpos($file, 'fixtures') !== FALSE) {
        $config_files[] = $file;
      }
    }, $files);

    $this->assertTrue(is_array($config_files));
    $this->assertCount(3, $config_files);
    $this->assertTrue(boolval(preg_match('/configuration\/\.project\/config\.yml$/', $config_files[0])), "The parent config files should be loaded first");
    $this->assertTrue(boolval(preg_match('/configuration\/withlocal\/\.project\/config\.yml$/', $config_files[1])), "The child config files should be loaded second to last");
    $this->assertTrue(boolval(preg_match('/configuration\/withlocal\/\.project\/config\.local\.yml$/', $config_files[2])), "The config.local.yml config file should be loaded last");
  }

  public function testGetConfigWithLocalFile() {
    $config = new Configuration(__DIR__ . '/fixtures/configuration/withlocal');
    $this->assertEquals('http://overwrite.example.com', $config->url->stage);
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
      [
        'input' => ['local.thing', 'local.style'],
        'expected' => 'vagrant',
      ],
      [
        'input' => ['nothing'],
        'expected' => 123,
        'default' => 123,
      ],
    ];

    foreach ($tests as $test) {
      $default = isset($test['default']) ? $test['default'] : NULL;
      $this->assertEquals($test['expected'], $config->getConfigOption($test['input'], $default));
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

  /**
   * Test Configuration::setConfig()
   */
  public function testSetConfig() {
    $config = new Configuration();
    $config->setConfig(['test' => 123]);

    $this->assertEquals(123, $config->test);
  }

  public function testSetConfigYaml() {
    $config = new Configuration();
    $config->setConfigYaml("local:\n  default:\n    style: docker");
    $this->assertEquals('docker', $config->getConfigOption('local.default.style'));
  }

  /**
   * Test Configuration::getStyleName()
   */
  public function testGetStyleName() {
    $config = new Configuration();
    $tests = [
      [
        'style' => 'test',
        'expect' => 'test',
      ],
      [
        'style' => 'test-thing',
        'expect' => 'test_thing',
      ],
    ];

    foreach ($tests as $test) {
      $this->assertEquals($test['expect'], $config->getStyleName($test['style']));
    }
  }

  public function testGetProviderClassWithDefault() {
    $config = new Configuration();
    $this->assertEquals('Project\Provider\ShellProvider', $config->getProviderClass());
  }
  
  public function testGetProviderClassWithConfiguredDefault() {
    $config = new TestConfiguration(__DIR__ . '/fixtures/configuration/example');
    $this->assertEquals('SomeProvider', $config->getProviderClass());
  }

  public function testGetCommands() {
    $config = new TestConfiguration(__DIR__ . '/fixtures/configuration/example');
    $commands = $config->getCommands();
    $this->assertTrue(in_array('CustomCommand', $commands), 'CustomCommand does not exist');
    $count = array_reduce($commands, function ($count, $command) {
      if ($command == 'CustomCommand') {
        return ++$count;
      }
      return $count;
    }, 0);
    $this->assertEquals(1, $count);
  }

  public function testGetEnvironment() {
    $app = new Application();
    $app->getDefinition()->addOption(
      new InputOption(
        'environment',
        'e',
        InputOption::VALUE_OPTIONAL,
        'The environment to operate in.'
      )
    );

    $config = new Configuration();

    $tests = [
      [
        'input' => [],
        'options' => [],
        'expect' => 'default',
      ],
      [
        'input' => [],
        'options' => ['environment' => 'test'],
        'expect' => 'test',
      ],
    ];

    foreach ($tests as $test) {
      $input = new ArrayInput($test['input']);
      $input->bind($app->getDefinition());
      foreach ($test['options'] as $option => $value) {
        $input->setOption($option, $value);
      }
      $this->assertEquals($test['expect'], $config->getEnvironment($input));
    }
  }

}
