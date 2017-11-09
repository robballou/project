<?php

namespace Project\Command\Deploy;

use Project\Base\ProjectCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Deploy an environment.
 */
class EnvCommand extends ProjectCommand {

  /**
   * Configure this command.
   */
  protected function configure() {
    $this
      // the name of the command (the part after "bin/console")
      ->setName('deploy')
      // the short description shown while running "php bin/console list"
      ->setDescription('Deploy changes to an environment')

      ->addArgument('environment', InputArgument::REQUIRED, 'The environment to deploy')
    ;
  }

  /**
   * {@inheritDoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $config = $this->getApplication()->config;
    $environment = $input->getArgument('environment');

    $deployment = $config->getConfigOption('deploy.' . $environment);
    if (!$deployment) {
      throw new \Exception('Could not find deployment: ' . $environment);
    }

    $style = $deployment->style;
    $provider = $this->getCommandProvider($style);
    $command = $provider->get($input, $output, $deployment);
    $ex = $this->getExecutor($command, $output);
    $ex->execute();
  }

}
