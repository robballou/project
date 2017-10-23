<?php

namespace Project\Provider;

use Project\ArrayObjectWrapper;
use Project\Provider\DockerProvider;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Provider for shell interface.
 */
class DockerComposeProvider extends DockerProvider {
  static public $styles = ['docker', 'docker-compose',];
  protected function command(ArrayObjectWrapper $details) {
    return 'docker-compose';
  }
}
