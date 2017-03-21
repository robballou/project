<?php

namespace Project\Test\Testable;

use Project\Configuration;

class TestConfiguration extends Configuration {
  public function testGetPathVariables() {
    $args = func_get_args();
    array_unshift($args, $this);
    return call_user_func_array([$this, 'getPathVariables'], $args);
  }

  public function testReplacePathVariables($path, Configuration $config = NULL) {
    return call_user_func([$this, 'replacePathVariables'], $path, $config);
  }

}
