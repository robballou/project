<?php

namespace Project;

class ArrayObjectWrapper implements \Iterator, \JsonSerializable {
  protected $array = [];

  public function __construct($array = NULL) {
    if ($array !== NULL) {
      if ($array instanceof ArrayObjectWrapper) {
        $array = $array->getArray();
      }
      $this->array = $array;
    }
  }

  public function __get($thing) {
    if (isset($this->array[$thing])) {
      if ($this->isAssociativeArray($this->array[$thing])) {
        return new ArrayObjectWrapper($this->array[$thing]);
      }
      return $this->array[$thing];
    }
    return NULL;
  }

  public function __set($thing, $value) {
    $this->array[$thing] = $value;
  }

  public function __isset($thing) {
    return isset($this->array[$thing]);
  }

  public function __unset($thing) {
    unset($this->array[$thing]);
  }

  public function current() {
    $item = current($this->array);
    if ($this->isAssociativeArray($item)) {
      $item = new ArrayObjectWrapper($item);
    }
    return $item;
  }

  public function rewind() {
    $item = reset($this->array);
    if ($this->isAssociativeArray($item)) {
      $item = new ArrayObjectWrapper($item);
    }
    return $item;
  }

  public function key() {
    return key($this->array);
  }

  public function next() {
    $item = next($this->array);
    if ($this->isAssociativeArray($item)) {
      $item = new ArrayObjectWrapper($item);
    }
    return $item;
  }

  public function valid() {
    return key($this->array) !== null;
  }

  public function getArray() {
    return $this->array;
  }

  public function getKeys() {
    return array_keys($this->array);
  }

  public function get($option, $default = NULL) {
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

  public function merge($option, $value) {
    if ($value instanceof ArrayObjectWrapper) {
      $value = $value->getArray();
    }

    if (!isset($this->array[$option])) {
      $this->$option = $value;
      return;
    }

    $option_value = $this->array[$option];
    if ($option_value instanceof ArrayObjectWrapper) {
      $option_value = $option_value->getArray();
    }


    if (is_array($option_value)) {
      $this->array[$option] = array_merge($option_value, $value);
    }
  }

  public function isAssociativeArray($thing) {
    if (!is_array($thing)) {
      return FALSE;
    }

    // check if this is a non-numeric array
    $keys = array_filter(array_keys($thing), function ($key) {
      if (!is_int($key)) {
        return TRUE;
      }
    });

    if ($keys) {
      return TRUE;
    }

    return FALSE;
  }

  public function jsonSerialize() {
    return $this->getArray();
  }

}
