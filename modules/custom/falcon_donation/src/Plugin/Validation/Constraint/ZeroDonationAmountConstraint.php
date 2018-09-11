<?php

namespace Drupal\falcon_donation\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * The constraint object.
 *
 * @Constraint(
 *   id = "falcon_donation__zero_donation_amount",
 *   label = @Translation("Zero donation amount", context = "Validation")
 * )
 */
class ZeroDonationAmountConstraint extends Constraint {

  /**
   * The error message for the constraint.
   *
   * @var string
   */
  public $message = 'Zero donation amount violation.';

}
