<?php

namespace Project\Base;

use Project\Base\ProjectCommand;
use Project\ArrayObjectWrapper;
use Project\Executor\Executor;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Base class for the local: commands.
 */
abstract class LocalBaseCommand extends ProjectCommand {

  /**
   * Apply some default values to the processed "thing".
   * 
   * TODO: this is nested loop should be refactored?
   */
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

  /**
   * Get the default "thing" (or things).
   * 
   * TODO: this sort of has dual principles when it should have one.
   * 
   * @param bool $all Return all components (returns the first one by default).
   */
  protected function getDefaultThings($all = FALSE) {
    $config = $this->getApplication()->config;

    // get any default configuration first.
    $default = $config->getConfigOption(['local.components.default', 'local.default']);

    if (!$all && $default) {
      return new ArrayObjectWrapper(['default' => $default]);
    }
    elseif (!$all && !$default) {
      return [];
    }

    $things = $config->getConfigOption('local.components');
    return $things;
  }

  /**
   * Execute a list of commands.
   *
   * @see pre()
   * @see post()
   */
  protected function executeList(InputInterface $input, OutputInterface $output, ArrayObjectWrapper $thing, $list_key) {
    $config = $this->getApplication()->config;
    $list = $thing->get($list_key, []);
    $list = new ArrayObjectWrapper($list);
    foreach ($list as $action) {
      $style = $action->get('style');
      $this->outputVerbose($output, $list_key . ' (' . $style . '): ' . var_export($action->getArray(), TRUE));
      if (!$style && $action->get(['command', 'script'])) {
        $style = 'shell';
      }
      
      $provider = $this->getCommandProvider($style);
      $command = $provider->get($input, $output, $action);
      $ex = $this->getExecutor($command, $output);
      $ex->execute();
    }
  }

  /**
   * Execute pre commands.
   *
   * @param InputInterface $input
   * @param OutputInterface $output
   * @param ArrayObjectWrapper $thing
   * @return void
   */
  protected function pre(InputInterface $input, OutputInterface $output, ArrayObjectWrapper $thing) {
    $this->executeList($input, $output, $thing, 'pre');
  }

  /**
   * Execute post commands.
   *
   * @param InputInterface $input
   * @param OutputInterface $output
   * @param ArrayObjectWrapper $thing
   * @return void
   */
  protected function post(InputInterface $input, OutputInterface $output, ArrayObjectWrapper $thing) {
    $this->executeList($input, $output, $thing, 'post');
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
      $things = $this->getDefaultThings($input->getOption('all', FALSE));
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
