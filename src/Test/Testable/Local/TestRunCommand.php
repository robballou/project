<?php

namespace Project\Test\Testable\Local;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Project\Command\Local\RunCommand;

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

}
