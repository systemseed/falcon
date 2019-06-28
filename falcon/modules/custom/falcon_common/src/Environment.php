<?php

namespace Drupal\falcon_common;

/**
 * Class Environment.
 */
class Environment implements EnvironmentInterface {

  /**
   * {@inheritdoc}
   */
  public function isProduction() {
    if (getenv(self::ENVIRONMENT) === self::ENVIRONMENT_PRODUCTION) {
      return TRUE;
    }

    return FALSE;
  }

}
