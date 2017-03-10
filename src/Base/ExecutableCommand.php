<?php

namespace Project\Base;

use Project\Base\ProjectCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

abstract class ExecutableCommand extends ProjectCommand {
  abstract protected function getExecutablePath();
}
