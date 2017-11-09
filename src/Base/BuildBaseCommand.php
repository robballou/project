<?php

namespace Project\Base;

use Project\Base\ProjectCommand;
use Project\ArrayObjectWrapper;

/**
 * Base class for build commands.
 */
abstract class BuildBaseCommand extends ProjectCommand {
  protected function getAdditionalBuildOptions() {
    $config = $this->getApplication()->config;
    $additional_build_options = [];
    if ($options = $config->get('options.build')) {
      if ($package_json_scripts = $this->validatePath($options->get('package_json_scripts', false), $config)) {
        $package_json = @json_decode(file_get_contents($package_json_scripts));
        if (isset($package_json->scripts)) {
          foreach ($package_json->scripts as $key => $value) {
            $additional_build_options[$key] = ['command' => 'npm run ' . escapeshellarg($key)];
          }
        }
      }
    }
    return new ArrayObjectWrapper($additional_build_options);
  }
}
