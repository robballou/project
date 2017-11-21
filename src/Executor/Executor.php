<?php

namespace Project\Executor;

use Project\Configuration;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Execute a command via passthru()
 */
class Executor {
  public $command = '';

  protected $pre = [];
  protected $sources = [];
  protected $post = [];

  public function __construct($command, OutputInterface $output = NULL, Configuration $config = NULL) {
    $this->command = $command;
    $this->output = $output;
    $this->config = $config;

    if ($this->config) {
      $pre = $this->config->getConfigOption('options.executor.pre');
      if ($pre) {
        $this->pre = $pre;
      }

      $sources = $this->config->getConfigOption('options.executor.sources');
      if ($sources) {
        $this->sources = $sources;
      }
    }
  }

  protected function buildCommand($command = NULL) {
    if (!$command && $this->command) {
      $command = $this->command;
    }

    if (!$command) {
      return $this;
    }

    foreach ($this->pre as $pre_command) {
      call_user_func($pre_command, $command);
    }

    $current_shell = getenv('SHELL');
    $shell = ($current_shell) ? $current_shell : 'bash';
    if ($this->config) {
      $shell = $this->config->getConfigOption('options.shell', $shell);
    }

    $real_command = $shell . ' -lc "';
    $real_command .= $this->escape($command) . '"';
    return $real_command;
  }

  public function execute($command = NULL) {
    $real_command = $this->buildCommand($command);
    $process = proc_open($real_command, array(0 => STDIN, 1 => STDOUT, 2 => STDERR), $pipes);
    $this->processStatus = proc_get_status($process);
    $this->exitCode = proc_close($process);
    return $this;
  }

  protected function escape($string) {
    return str_replace('"', '\\"', $string);
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
