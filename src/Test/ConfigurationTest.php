<?php

namespace Project\Test;

use PHPUnit\Framework\TestCase;
use Project\Configuration;

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
}
