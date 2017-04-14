<?php

namespace Project\Traits;

trait RunnerTrait {
  protected function getRunner($thing) {
    $style = $thing->style;
    if (!$style) {
      if (isset($thing->command)) {
        $style = 'command';
      }
      elseif (isset($thing->script)) {
        $style = 'script';
      }
      else {
        throw new \Exception('Invalid style for this local component: ' . json_encode($thing));
      }
    }

    if (isset($thing->runner)) {
      return $thing->runner;
    }

    $map = [
      'vagrant' => 'Project\Runner\VagrantRunner',
      'docker-compose' => 'Project\Runner\DockerComposeRunner',
      'docker' => 'Project\Runner\DockerRunner',
      'script' => 'Project\Runner\ScriptRunner',
      'command' => 'Project\Runner\CommandRunner',
    ];

    if (in_array($style, array_keys($map))) {
      return $map[$style];
    }

    throw new Exception('Could not find runner for: ' . $style);
  }
}
