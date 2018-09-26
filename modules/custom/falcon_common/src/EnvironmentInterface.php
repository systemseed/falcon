<?php

namespace Drupal\falcon_common;

/**
 * Interface EnvironmentInterface.
 */
interface EnvironmentInterface {

  const ENVIRONMENT = 'ENVIRONMENT';
  const ENVIRONMENT_PRODUCTION = 'production';

  /**
   * Check if environment is running in production.
   *
   * @return bool
   *   TRUE if environment is running in production.
   */
  public function isProduction();

}
