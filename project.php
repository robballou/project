<?php

require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Console\Application;
use Project\Command\LocalRunCommand;
use Project\Command\DrushCommand;
use Project\Command\ConnectCommand;
use Project\Command\DrupalConsoleCommand;
use Project\Command\Config\SourcesCommand;
use Project\Command\Config\ListCommand;

$application = new Application();

$application->add(new LocalRunCommand());
$application->add(new SourcesCommand());
$application->add(new ListCommand());
$application->add(new DrushCommand());
$application->add(new DrupalConsoleCommand());
$application->add(new ConnectCommand());

$application->run();
