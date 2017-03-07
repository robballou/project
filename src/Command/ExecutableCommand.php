<?php

namespace Project\Command;

use Project\Command\ProjectCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

abstract class ExecutableCommand extends ProjectCommand {
  abstract protected function getExecutablePath();
}
