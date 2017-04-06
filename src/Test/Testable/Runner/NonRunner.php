<?php

namespace Project\Test\Testable\Runner;

use Project\Runner\Runner;

class NonRunner extends Runner {
  public function run() {
    if ($this->output) {
      $this->output->writeln('run ' . $this->thing->style);
    }
  }

  public function stop() {
    if ($this->output) {
      $this->output->writeln('stop ' . $this->thing->style);
    }
  }

}
