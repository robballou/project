<?php

namespace Project\Test\Testable\Command\Local;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Project\Command\Local\RunCommand;
use Project\Test\Testable\Executor\NonExecutor;

/**
 * Make a testable ProjectCommand
 */
class TestRunCommand extends RunCommand {
  public function noExecute() {
    return;
  }

  public function testableResolveThings(InputInterface $input, OutputInterface $output) {
    $this->setCode([$this, 'noExecute']);
    $this->run($input, $output);
    return $this->resolveThings($input, $output);
  }

  protected function getRunner($thing) {
    return 'Project\Test\Testable\Runner\NonRunner';
  }

  protected function getExecutor($command, OutputInterface $output = NULL) {
    if (!$output && $this->output) {
      $output = $this->output;
    }
    return new NonExecutor($command, $output);
  }

}
