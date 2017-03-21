<?php

namespace Project\Base;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

// use Project\Trait\PathTrait;

// use Symfony\Component\Console\Input\InputInterface;
// use Symfony\Component\Console\Input\InputArgument;

/**
 * Base command support
 */
abstract class ProjectCommand extends Command {

  use \Project\Traits\PathTrait;

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

}
