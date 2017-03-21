<?php

namespace Project\Runner;

use Project\Configuration;
use Project\Executor\Executor;
use Symfony\Component\Console\Output\OutputInterface;

abstract class Runner {
  use \Project\Traits\PathTrait;

  protected $config;
  protected $thing;

  public function __construct(Configuration $config, $thing, OutputInterface $output = NULL) {
    $this->config = $config;
    $this->thing = $thing;
    $this->output = $output;
  }

  protected function getExecutor($command) {
    $ex = new Executor($command);
    if ($this->isVerbose()) {
      $ex->outputCommand($this->output);
    }
    return $ex;
  }

  protected function isVerbose() {
    if (!$this->output) {
      return FALSE;
    }

    return $this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE;
  }

  abstract public function run();

  abstract public function stop();

}
