<?php

namespace Project\Command;

use Project\Base\ProjectCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class ScriptCommand extends ProjectCommand {
  protected function configure() {
    $this
      // the name of the command (the part after "bin/console")
      ->setName('script')
      // the short description shown while running "php bin/console list"
      ->setDescription('Run a script')

      ->addArgument('name', InputArgument::REQUIRED, 'Name of script to run')
      ->addArgument('args', InputArgument::IS_ARRAY | InputArgument::OPTIONAL, 'Optional args to include')
      ->setAliases(['scr'])
      ->ignoreValidationErrors()
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $config = $this->getApplication()->config;
    $environment = $config->getEnvironment($input);

    // get the first one of the following options to figure out the style of
    // connection...
    $name = $input->getArgument('name');
    $script = $config->getConfigOption([
      'script.' . $environment . '.' . $name,
      'script.' . $name,
    ]);

    if (!$script) {
      throw new \Exception('Could not find script: ' . $name);
    }

    // $this_command = '';
    //
    // $path = $this->validatePath($script->script, $config);
    // if (isset($script->base)) {
    //   $this_command = 'cd ' . $script->base . ' && ';
    // }
    //
    // $this_command = $path;
    //
    // if ($args = $input->getArgument('args')) {
    //   $this_command .= ' ' . implode(' ', $args);
    // }
    $provider = $this->getCommandProvider('shell');
    $this_command = $provider->get($input, $output, $script, 'exec');

    $ex = $this->getExecutor($this_command, $output);
    $ex->execute();
  }

}
