<?php

namespace Drupal\falcon_common;

/**
 * Interface PaymentModeInterface.
 */
interface PaymentModeInterface {

  /**
   * Check if payment gateways in test mode are allowed.
   *
   * @return bool
   *   TRUE if test mode is allowed.
   */
  public function isTestModeAllowed();

}
