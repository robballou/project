<?php

namespace Project\Traits;

use Project\Configuration;

trait PathTrait {
  /**
   * Get the current directory.
   */
  protected function getCurrentDirectory() {
    if ($pwd = getenv('PWD')) {
      return $pwd;
    }
    if (isset($_SERVER['PWD'])) {
      return $_SERVER['PWD'];
    }
    return getcwd();
  }

  protected function getPathVariables(Configuration $config = NULL) {
    $variables = [];

    if ($config) {
      $variables['HOME'] = $config->getUserHome();
      $variables['PROJECT'] = $config->getProjectPath();
    }

    return $variables;
  }

  /**
   * Return the user's home directory.
   *
   * This is stolen from drush: https://github.com/drush-ops/drush
   */
  public function getUserHome() {
    // Cannot use $_SERVER superglobal since that's empty during UnitUnishTestCase
    // getenv('HOME') isn't set on Windows and generates a Notice.
    $home = getenv('HOME');
    if (!empty($home)) {
      // home should never end with a trailing slash.
      $home = rtrim($home, '/');
    }
    elseif (!empty($_SERVER['HOMEDRIVE']) && !empty($_SERVER['HOMEPATH'])) {
      // home on windows
      $home = $_SERVER['HOMEDRIVE'] . $_SERVER['HOMEPATH'];
      // If HOMEPATH is a root directory the path can end with a slash. Make sure
      // that doesn't happen.
      $home = rtrim($home, '\\/');
    }
    return empty($home) ? NULL : $home;
  }

  /**
   * Replace project variables in a path.
   */
  protected function replacePathVariables($path, Configuration $config = NULL) {
    $variables = $this->getPathVariables($config);
    $variable_names = array_map(function ($name) {
      return '$' . $name;
    }, array_keys($variables));
    $variable_values = array_values($variables);
    if ($home = $this->getUserHome()) {
      array_push($variable_names, '~');
      array_push($variable_values, $home);
    }
    return str_replace($variable_names, $variable_values, $path);
  }

  protected function validatePath($path, Configuration $config = NULL) {
    return realpath($this->replacePathVariables($path, $config));
  }

}
