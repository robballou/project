<?php

namespace Project\Executor;

use Project\Configuration;
use Project\Executor\Executor;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Only output a command.
 */
class OutputExecutor extends Executor {
  /**
   * Override execute to only output the command.
   */
  public function execute($command = NULL) {
    $real_command = $this->buildCommand($command);
    $this->output->writeln($real_command);
    return $this;
  }

}
