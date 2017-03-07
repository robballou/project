<?php

namespace Project;

use Project\ArrayObjectWrapper;
use Symfony\Component\Console\Input\InputInterface;

class Configuration extends ArrayObjectWrapper {
  protected $files = [];
  protected $startingDirectory = NULL;

  public function __construct($directory = NULL) {
    $this->getConfig($directory);
  }

  public function getEnvironment(InputInterface $input) {
    if ($environment = $input->getOption('environment')) {
      return $environment;
    }
    return 'default';
  }

  /**
   * Return the user's home directory.
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

  public function getConfigFiles($current_directory = NULL) {
    // check if the user's home directory has a configuration file

    if ($this->files) {
      return $this->files;
    }

    $config_files = [];
    if (file_exists($this->getUserHome() . '/.project/config.yml')) {
      $config_files[] = $this->getUserHome() . '/.project/config.yml';
    }

    if (!$current_directory) {
      $current_directory = getcwd();
    }

    if (!file_exists($current_directory)) {
      throw new \Exception('Invalid directory: ' . $current_directory);
    }

    $this->startingDirectory = $current_directory;

    $last = NULL;
    while ($current_directory) {
      if (file_exists($current_directory . '/.project/config.yml')) {
        $config_files[] = $current_directory . '/.project/config.yml';
      }
      $last = $current_directory;
      $current_directory = dirname($current_directory);
      if ($last !== NULL && $current_directory == $last) {
        break;
      }
    }

    $config_files = array_reverse($config_files);
    $this->files = $config_files;
    return $config_files;
  }

  public function getCommandConfig($command = NULL, InputInterface $input = NULL) {
    if (isset($this->$command)) {
      if ($input && $environment = $input->getOption('environment')) {
        if (isset($this->$command->$environment)) {
          // maybe this should merge default?
          return $this->$command->$environment;
        }
        throw new \Exception('Invalid environment specified: ' . $environment);
      }
      elseif (isset($this->$command->default)) {
        return $this->$command->default;
      }

      return $this->$command;
    }
    return NULL;
  }

  public function getConfig($directory = NULL) {
    if ($this->array) {
      return $this->array;
    }

    $config = [];

    foreach ($this->getConfigFiles($directory) as $config_file) {
      $this_config = @yaml_parse_file($config_file);
      if (!$this_config) {
        fwrite(STDERR, 'WARNING: Could not load configuration file: ' . $config_file . "\n");
        continue;
      }
      $config = array_merge($config, $this_config);
    }

    $this->array = $config;
    return $this->array;
  }

  public function getConfigOption($option) {
    if (!is_array($option)) {
      $option = [$option];
    }

    foreach ($option as $this_option) {
      try {
        $pieces = explode('.', $this_option);
        $current = $this;
        foreach ($pieces as $piece) {
          if (isset($current->$piece)) {
            $current = $current->$piece;
            continue;
          }
          throw new \Exception('Cannot find piece');
        }

        return $current;
      }
      catch (\Exception $e) {

      }
    }

    return NULL;
  }
}
