<?php

namespace Project\Test;

use Project\Command\UrlCommand;
use Project\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class UrlCommandTest extends TestCase {
  public function setUp() {
    $this->application = new Application();
    $this->application->config = new Configuration(__DIR__ . '/fixtures/configuration/example');
    $this->application->add(new UrlCommand());

    $this->command = $this->application->find('url');
    $this->commandTester = new CommandTester($this->command);
  }

  protected function assertUrlExists($url_key, $output) {
    $output = array_filter(explode("\n", $output));
    $commands = [];
    array_map(function ($line) use (&$commands) {
      if (preg_match('/^([^:]+):\s*(.+)$/', $line, $matches)) {
        $commands[$matches[1]] = $matches[2];
      }
    }, $output);
    $this->assertTrue(isset($commands[$url_key]), 'The URL ' . $url_key . ' was not present');
  }

  protected function countUrls($output) {
    $output = array_filter(explode("\n", $output));
    if (!is_array($output)) {
      $output = [$output];
    }
    return count($output);
  }

  public function testDefaultUrls() {
    $this->commandTester->execute([
      'command' => $this->command->getName(),
    ]);
    $output = $this->commandTester->getDisplay();
    $this->assertFalse(empty($output));

    // check the number of lines we have
    $this->assertEquals(2, $this->countUrls($output));
    $this->assertUrlExists('stage', $output);
  }

  public function testSpecificUrl() {
    $this->commandTester->execute([
      'command' => $this->command->getName(),
      'name' => ['stage'],
    ]);
    $output = $this->commandTester->getDisplay();
    $this->assertFalse(empty($output));

    // check the number of lines we have
    $this->assertEquals(1, $this->countUrls($output));
    $this->assertEquals('http://staging.example.com', trim($output));
  }

  public function testSpecificUrls() {
    $this->commandTester->execute([
      'command' => $this->command->getName(),
      'name' => ['stage', 'prod'],
    ]);
    $output = $this->commandTester->getDisplay();
    $this->assertFalse(empty($output));

    // check the number of lines we have
    $this->assertEquals(2, $this->countUrls($output));
    $this->assertUrlExists('stage', $output);
    $this->assertUrlExists('prod', $output);
  }

}
