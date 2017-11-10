<?php

namespace Project\Test;

use Project\Test\Testable\Command\Deploy\TestEnvCommand;
use Project\Test\Testable\TestSingleDirectoryConfiguration;
use Project\Test\ProjectTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\StreamOutput;

class DeployTest extends ProjectTestCase {
  public function setUp() {
    parent::setUp();
    
    $this->application->config = new TestSingleDirectoryConfiguration(__DIR__ . '/fixtures/configuration/deploy');
    $this->application->add(new TestEnvCommand());
    $this->command = $this->application->find('deploy');
    $this->tester = new CommandTester($this->command);
  }

  public function testNoEnvironment() {
    $this->expectException(\Exception::class);
    $this->tester->execute([
      'command' => 'deploy',
      'environment' => 'xcv',
    ]);
    $output = trim($this->tester->getDisplay());
  }

  public function testScriptDeploy() {
    $this->tester->execute([
      'command' => 'deploy',
      'environment' => 'script',
    ]);
    $output = trim($this->tester->getDisplay());
    $this->assertEquals('./deploy.sh', $output);
  }

  public function testAnsibleDeploy() {
    $this->tester->execute([
      'command' => 'deploy',
      'environment' => 'ansible',
    ]);
    $output = trim($this->tester->getDisplay());
    $this->assertEquals("ansible-playbook -i 'hosts' 'site.yml'", $output);
  }

}
