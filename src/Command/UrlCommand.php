<?php

namespace Project\Command;

use Project\Base\ProjectCommand;
use Project\ArrayObjectWrapper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class UrlCommand extends ProjectCommand {
  protected function configure() {
    $this
      // the name of the command (the part after "bin/console")
      ->setName('url')

      // the short description shown while running "php bin/console list"
      ->setDescription('Get a URL for this project (will open if possible)')

      ->addArgument('name', InputArgument::IS_ARRAY | InputArgument::OPTIONAL, 'Optional url name(s) to return')
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $names = $input->getArgument('name');

    $all_names = $this->getApplication()->config->getConfigOption('url');

    // figure out if the user specified a URL or if we are getting all of them
    if (!$names) {
      $names = $all_names;
    }
    else {
      $found = FALSE;

      $requested_names = $names;
      $names = [];
      foreach ($all_names as $key => $value) {
        if (in_array($key, $requested_names)) {
          $names[$key] = $value;
          $found = TRUE;
        }
      }
      $names = new ArrayObjectWrapper($names);

      if (!$found) {
        return;
      }
    }

    // nothing going on...
    if (!$names) {
      return;
    }

    if (count($names->getArray()) > 1) {
      $keys = array_keys($names->getArray());
      $longest = 0;
      foreach ($keys as $key) {
        if (strlen($key) > $longest) {
          $longest = strlen($key);
        }
      }

      $longest += 3;

      foreach ($names as $name => $value) {
        $output->writeln(sprintf("%-${longest}s", $name . ":") . "$value");
      }
      return;
    }

    $values = array_values($names->getArray());
    $output->writeln($values[0]);
  }
}
