<?php

namespace Project\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

abstract class ProjectCommand extends Command {
  protected $config = NULL;
  protected $command_config = NULL;

  protected function getEnvironment(InputInterface $input) {
    if ($environment = $input->getOption('environment')) {
      return $environment;
    }
    return 'default';
  }

  /**
   * Return the user's home directory.
   */
  protected function getUserHome() {
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

  protected function getCommandConfig($command = NULL, InputInterface $input = NULL) {
    if (!$command && !$this->command_config) {
      return NULL;
    }

    $config = $this->getConfig();
    if (isset($config[$command])) {
      if ($environment = $input->getOption('environment')) {
        if (isset($config[$command][$environment])) {
          // maybe this should merge default?
          return $config[$command][$environment];
        }
        throw new \Exception('Invalid environment specified: ' . $environment);
      }
      elseif (isset($config[$command]['default'])) {
        return $config[$command]['default'];
      }

      return $config[$command];
    }
    return NULL;
  }

  protected function getConfigFiles() {
    // check if the user's home directory has a configuration file

    $config_files = [];
    if (file_exists($this->getUserHome() . '/.project/config.yml')) {
      $config_files[] = $this->getUserHome() . '/.project/config.yml';
    }

    $current = getcwd();
    $last = NULL;
    while ($current) {
      if (file_exists($current . '/.project/config.yml')) {
        $config_files[] = $current . '/.project/config.yml';
      }
      $last = $current;
      $current = dirname($current);
      if ($last !== NULL && $current == $last) {
        break;
      }
    }

    return $config_files;
  }

  protected function getConfig() {
    if ($this->config) {
      return $this->config;
    }

    $config = [];

    foreach ($this->getConfigFiles() as $config_file) {
      $this_config = yaml_parse_file($config_file);
      $config = array_merge($config, $this_config);
    }

    $this->config = $config;
    return $this->config;
  }

  protected function getConfigOption($option) {
    $config = $this->getConfig();
    if (!$config) {
      return NULL;
    }

    $pieces = explode('.', $option);
    $current = $config;
    foreach ($pieces as $piece) {
      if (isset($current[$piece])) {
        $current = $current[$piece];
        continue;
      }
      return NULL;
    }

    return $current;
  }
}
