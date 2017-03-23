<?php

namespace Project\Executor;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * Execute a command via passthru()
 */
class Executor {
  public $command = '';

  protected $pre = [];
  protected $post = [];

  public function __construct($command, OutputInterface $output = NULL) {
    $this->command = $command;
    $this->output = $output;
  }

  public function execute($command = NULL) {
    if (!$command && $this->command) {
      $command = $this->command;
    }

    if (!$command) {
      return $this;
    }

    foreach ($this->pre as $pre_command) {
      call_user_func($pre_command, $command);
    }

    $process = proc_open($command, array(0 => STDIN, 1 => STDOUT, 2 => STDERR), $pipes);
    $this->processStatus = proc_get_status($process);
    $this->exitCode = proc_close($process);
    return $this;
  }

  protected function output($command) {
    if ($this->output) {
      $this->output->writeln($command);
    }
  }

  public function outputCommand(OutputInterface $output = NULL) {
    if ($output) {
      $this->output = $output;
    }
    $this->pre[] = [$this, 'output'];
    return $this;
  }

}
