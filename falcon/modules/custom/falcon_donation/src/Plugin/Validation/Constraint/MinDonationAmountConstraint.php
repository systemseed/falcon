<?php

namespace Drupal\falcon_donation\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * The constraint object.
 *
 * @Constraint(
 *   id = "falcon_donation__min_donation_amount",
 *   label = @Translation("Minimal donation amount", context = "Validation")
 * )
 */
class MinDonationAmountConstraint extends Constraint {

  /**
   * The error message for the constraint.
   *
   * @var string
   */
  public $message = 'Minimal donation amount violation.';

}
