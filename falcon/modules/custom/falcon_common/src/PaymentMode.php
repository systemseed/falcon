<?php

namespace Drupal\falcon_common;

use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class PaymentMode.
 */
class PaymentMode implements PaymentModeInterface {

  /**
   * The current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * The environment.
   *
   * @var \Drupal\falcon_common\EnvironmentInterface
   */
  protected $environment;

  /**
   * Constructs a new Payment object.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   * @param \Drupal\falcon_common\EnvironmentInterface $environment
   *   The environment.
   */
  public function __construct(RequestStack $request_stack, EnvironmentInterface $environment) {
    $this->request = $request_stack->getCurrentRequest();
    $this->environment = $environment;
  }

  /**
   * {@inheritdoc}
   */
  public function isTestModeAllowed() {
    // Allow for all non-production environments.
    if (!$this->environment->isProduction()) {
      return TRUE;
    }

    // Check payment secret header.
    $payment_secret_header_value = $this->request->headers->get(getenv('PAYMENT_SECRET_HEADER_NAME'));
    if (!$payment_secret_header_value) {
      return FALSE;
    }
    if ($payment_secret_header_value == getenv('PAYMENT_SECRET_HEADER_VALUE')) {
      return TRUE;
    }

    return FALSE;
  }

}
