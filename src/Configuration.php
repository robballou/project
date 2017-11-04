<?php

namespace Project;

use Project\ArrayObjectWrapper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Project configuration handler.
 */
class Configuration extends ArrayObjectWrapper {
  use \Project\Traits\PathTrait;

  protected $array = [];
  protected $files = [];
  protected $startingDirectory = NULL;

  protected $providers = [];
  protected $commands = [];

  /**
   * Constructor.
   */
  public function __construct($directory = NULL) {
    $this->getConfig($directory);
  }

  /**
   * Get the environment for the current command.
   *
   * @param InputInterface $input
   * @return string
   */
  public function getEnvironment(InputInterface $input) {
    if ($environment = $input->getOption('environment')) {
      return $environment;
    }
    return 'default';
  }

  /**
   * Get the config files based on a directory path.
   *
   * @param string $current_directory
   * @return array
   */
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
      if (file_exists($current_directory . '/.project/config.local.yml')) {
        $config_files[] = $current_directory . '/.project/config.local.yml';
      }
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

  /**
   * Get the configuration for a given command.
   * 
   * If not available/not found, it will return NULL. Otherwise it will be an
   * ArrayObjectWrapper.
   *
   * @param string $command
   * @param InputInterface $input
   * @return mixed
   */
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

  /**
   * Get available commands.
   * 
   * Available options:
   * 
   * - directory: used to specify the starting directory.
   * - current: the current directory to search.
   * 
   * @param array $options
   */
  public function getCommands(array $options = []) {
    if (isset($options['reset'])) {
      $this->commands = [];
    }

    $directory = __DIR__;
    if (isset($options['directory'])) {
      $directory = $options['directory'];
    }

    if (!isset($options['current']) && file_exists($directory . '/Command')) {
      return $this->getCommands(['current' => $directory . '/Command']);
    }

    // return statically cached commands if they exist
    if (isset($options['current']) && isset($this->commands[$options['current']])) {
      return $this->commands[$options['current']];
    }

    $commands = [];
    $current = $options['current'];

    // get the files from the directory
    $entries = $this->scanDir($current);

    $source_path = array_reverse(explode('/', $current));
    while ($source_path[0] != 'Command') {
      array_shift($source_path);
    }
    $source_path = implode('/', array_reverse($source_path));

    foreach ($entries as $entry) {
      $class = str_replace('.php', '', basename($entry));
      $path = str_replace([$source_path, '/'], ['', '\\'], dirname($entry));
      $namespace = 'Project\Command' . $path . '\\' . $class;
      $commands[] = $namespace;
    }
    
    // find custom commands
    if ($custom_commands = $this->getConfigOption('options.commands')) {
      $commands = array_merge($commands, $custom_commands);
    }

    // find custom passalong commands
    if ($passalong_commands = $this->getConfigOption('options.passalong')) {
      foreach ($passalong_commands as $command => $details) {
        $commands[] = ['Project\Command\PassAlongCommand', $command];
      }
    }

    $commands = array_unique($commands, SORT_REGULAR);
    $this->commands[$current] = $commands;
    return $this->commands[$current];
  }

  /**
   * Load configuration from a directory.
   *
   * @param string $directory
   *   Optional directory to load configuration from. See
   *   Configuration::getConfigFiles() for more info.
   */
  public function getConfig($directory = NULL) {
    if ($this->array) {
      return $this->array;
    }

    $config = [];

    foreach ($this->getConfigFiles($directory) as $config_file) {
      $this_config = Yaml::parse(file_get_contents($config_file));
      if (!$this_config) {
        fwrite(STDERR, 'WARNING: Could not load configuration file: ' . $config_file . "\n");
        continue;
      }
      $config = array_merge($config, $this_config);
    }

    $this->setConfig($config);
    return $this->array;
  }

  /**
   * Get a configuration option based on a path.
   *
   * @param mixed $option An array for that path or a string specifying the path to a configuration option.
   * @param mixed $default The default value if not found.
   * @return mixed
   */
  public function getConfigOption($option, $default = NULL) {
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

  /**
   * Get style name.
   *
   * @param string $style
   * @return string
   */
  public function getStyleName($style) {
    return str_replace('-', '_', $style);
  }

  /**
   * Get the project path.
   *
   * @return string
   */
  public function getProjectPath() {
    $base = $this->getConfigOption('base');
    if (!$base) {
      $files = $this->getConfigFiles();
      $last_file = array_pop($files);
      $base = dirname(dirname($last_file));
    }
    return $base;
  }

  /**
   * Get the provider class for a given style.
   *
   * @param string $style
   * @return string
   */
  public function getProviderClass($style = NULL) {
    $default_providers = [
      'default' => 'Project\Provider\ShellProvider',
      'shell' => 'Project\Provider\ShellProvider',
      'drush' => 'Project\Provider\Drupal\DrupalCommandProvider',
      'docker-compose' => 'Project\Provider\DockerComposeProvider',
      'ssh' => 'Project\Provider\SSHProvider',
      'vagrant' => 'Project\Provider\VagrantProvider',
    ];

    if (!$style) {
      $style = 'default';
    }

    $configured_providers = $this->getConfigOption('options.providers', new ArrayObjectWrapper);
    $providers = array_merge($default_providers, $configured_providers->getArray());
    if (isset($providers[$style])) {
      return $providers[$style];
    }
    
    return $providers['default'];
  }

  /**
   * Recursively get all files in the directory.
   */
  public function scanDir($dir) {
    $entries = [];
    $ignore = ['node_modules', 'vendor'];
    foreach (scandir($dir) as $entry) {
      if ($entry[0] == '.' || in_array($entry, $ignore)) {
        continue;
      }

      $entry_path = $dir . DIRECTORY_SEPARATOR . $entry;
      if (is_dir($entry_path)) {
        $entries = array_merge($entries, $this->scanDir($entry_path));
        continue;
      }

      $entries[] = $entry_path;
    }

    return $entries;
  }

  /**
   * Set the configuration array.
   */
  public function setConfig(array $config) {
    $this->array = $config;
  }

  /**
   * Set configuration array with some Yaml
   *
   * @param string $yaml
   * @return void
   */
  public function setConfigYaml($yaml) {
    return $this->setConfig(Yaml::parse($yaml));
  }

}
