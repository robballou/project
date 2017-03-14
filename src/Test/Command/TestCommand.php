<?php

namespace Project\Test\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Project\Base\ProjectCommand;

/**
 * Make a testable ProjectCommand
 */
class TestCommand extends ProjectCommand {

  protected function configure() {
    $this
      // the name of the command (the part after "bin/console")
      ->setName('test')
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output) {

  }

  public function testIsNonAssocativeArray($array) {
    return $this->isNonAssociativeArray($array);
  }



}
