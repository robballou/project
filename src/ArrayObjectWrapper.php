<?php

namespace Project;

class ArrayObjectWrapper {
  protected $array = [];

  public function __construct($array = NULL) {
    if ($array !== NULL) {
      $this->array = $array;
    }
  }

  public function __get($thing) {
    if (isset($this->array[$thing])) {
      if (is_array($this->array[$thing])) {
        $this_thing = $this->array[$thing];
        $keys = array_filter(array_keys($this_thing), function ($key) {
          if (!is_int($key)) {
            return TRUE;
          }
        });
        if ($keys) {
          return new ArrayObjectWrapper($this_thing);
        }
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

  public function getArray() {
    return $this->array;
  }

}
