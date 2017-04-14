<?php

namespace Project\Base;

use Project\Base\ProjectCommand;
use Project\ArrayObjectWrapper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class LocalBaseCommand extends ProjectCommand {

  protected function applyDefaultValues($default, &$processed_things) {
    if ($default) {
      foreach (array_keys($processed_things) as $key) {
        foreach ($default as $default_key => $default_value) {
          if (!isset($processed_things[$key]->$default_key)) {
            $processed_things[$key]->$default_key = $default_value;
          }
        }
      }
    }
  }

  protected function getDefaultThings() {
    $config = $this->getApplication()->config;

    // get any default configuration first.
    $default = $config->getConfigOption('local.default');

    $things = $config->getConfigOption('local.components');
    if (!$things && $default) {
      $things = new ArrayObjectWrapper(['default' => $default]);
    }

    return $things;
  }

  /**
   * Figure out the "things" to use for this local command.
   */
  protected function resolveThings(InputInterface $input, OutputInterface $output) {
    $config = $this->getApplication()->config;

    // get the requested things from the user
    $things = $input->getArgument('thing');

    // get any default configuration first.
    $default = $config->getConfigOption('local.default');

    // if the user did not specify things, we will automatically update them.
    if (!$things) {
      $things = $this->getDefaultThings();
    }

    // at this point, if we can't find anything to run, then we shouldn't
    // run anything
    if (!$things) {
      $output->writeln('No things to run');
      return;
    }
    // also check that we're dealing with ArrayObjectWrapper's instead
    // of arrays.
    elseif (is_array($things)) {
      $things = new ArrayObjectWrapper($things);
    }

    // we now have a list of things, which may be an array of keys
    // ['default', 'frontend'] or it may be an array of fully fledged
    // things ['default' => ['style' => '...']] so we need to process
    // these into a single known thing.
    $processed_things = [];
    foreach ($things as $key => $thing) {
      // if thing is a string, then it is the key of what we are after...
      if (is_string($thing)) {
        $this->outputVerbose($output, 'Finding ' . $thing);
        $processed_things[$thing] = $config->getConfigOption('local.components.' . $thing);

        if (!$processed_things[$thing] && $thing == 'default' && $default) {
          $processed_things[$thing] = $default;
        }

        // if we couldn't find the thing in local.components throw an exception
        // with the valid components for this project...
        if (!$processed_things[$thing]) {
          $components = $config->getConfigOption('local.components');
          $valid_components = [];
          if ($components) {
            foreach ($components->getKeys() as $key) {
              $valid_components[] = $key;
            }
            sort($valid_components);
          }
          $valid_components = $valid_components ? implode(', ', $valid_components) : '(no components available)';
          throw new \Exception('Could not find component: ' . $thing . '. Valid components: ' . $valid_components);
        }
        continue;
      }
      $processed_things[$key] = $thing;
    }

    // apply default values
    $this->applyDefaultValues($default, $processed_things);

    return new ArrayObjectWrapper($processed_things);
  }

}
