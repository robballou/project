<?php

namespace Project\Base;

use Symfony\Component\Console\Command\Command;

// use Symfony\Component\Console\Input\InputInterface;
// use Symfony\Component\Console\Output\OutputInterface;
// use Symfony\Component\Console\Input\InputArgument;

/**
 * Base command support
 */
abstract class ProjectCommand extends Command {

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

}
