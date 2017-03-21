<?php

namespace Project;

use Project\ArrayObjectWrapper;
use Symfony\Component\Console\Input\InputInterface;

class Configuration extends ArrayObjectWrapper {
  use \Project\Traits\PathTrait;

  protected $array = [];
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
          $command_config = new ArrayObjectWrapper($this->$command->$environment);
          if (isset($this->$command->options)) {
            $command_config->merge('options', $this->$command->options);
          }
          return $command_config;
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

  public function getCommands(array $options = []) {
    $directory = __DIR__;
    if (isset($options['directory'])) {
      $directory = $options['directory'];
    }

    if (!isset($options['current']) && file_exists($directory . '/Command')) {
      return $this->getCommands(['current' => $directory . '/Command']);
    }

    $commands = [];
    $d = dir($options['current']);
    $source_path = array_reverse(explode('/', $options['current']));
    while ($source_path[0] != 'Command') {
      array_shift($source_path);
    }
    $source_path = implode('/', array_reverse($source_path));

    while (FALSE !== ($entry = $d->read())) {
      if (substr($entry, 0, 1) == '.') {
        continue;
      }
      $entry_path = $options['current'] . '/' . $entry;
      if (is_file($entry_path) && preg_match('/Command.php$/', $entry)) {
        $class = str_replace('.php', '', basename($entry_path));
        $path = str_replace([$source_path, '/'], ['', '\\'], dirname($entry_path));
        $namespace = 'Project\Command' . $path . '\\' . $class;
        $commands[] = $namespace;
      }
      elseif (is_dir($entry_path)) {
        $commands = array_merge($commands, $this->getCommands(['current' => $entry_path]));
      }
    }

    if ($custom_commands = $this->getConfigOption('commands')) {
      $commands = array_merge($commands, $custom_commands);
    }

    return $commands;
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

  public function getConfigOption($option, $default=NULL) {
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

    return $default;
  }
}
