<?php

namespace Project\Base;

use Project\Executor\Executor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Base command support
 */
abstract class ProjectCommand extends Command {
  use \Project\Traits\PathTrait;
  use \Project\Traits\RunnerTrait;

  protected function getExecutor($command, OutputInterface $output = NULL) {
    if (!$output && $this->output) {
      $output = $this->output;
    }

    var_dump($this->getApplication()->config);
    $ex = new Executor($command, $output, $this->getApplication()->config);
    if ($output && $output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
      $ex->outputCommand($output);
    }
    return $ex;
  }

  /**
   * Check if the array is associative...
   */
  protected function isNonAssociativeArray($array) {
    $non_numeric_keys = array_filter(array_keys($array), function ($key) {
      return !is_numeric($key);
    });
    if ($non_numeric_keys) {
      return FALSE;
    }
    return TRUE;
  }

  /**
   * Check if the command is running in verbose mode.
   */
  protected function isVerbose(OutputInterface $output) {
    return $output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE;
  }

  /**
   * Write out a message if we are verbose...
   */
  protected function outputVerbose(OutputInterface $output, $message) {
    if ($this->isVerbose($output)) {
      $output->writeln($message);
    }
  }

  /**
   * Overrides the parent to store the output/inputs
   */
  public function run(InputInterface $input, OutputInterface $output) {
    $this->input = $input;
    $this->output = $output;
    return parent::run($input, $output);
  }

}
