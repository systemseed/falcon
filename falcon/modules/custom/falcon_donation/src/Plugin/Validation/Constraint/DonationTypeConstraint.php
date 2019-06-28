<?php

namespace Drupal\falcon_donation\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * The constraint object.
 *
 * @Constraint(
 *   id = "falcon_donation__donation_type",
 *   label = @Translation("Donation type", context = "Validation")
 * )
 */
class DonationTypeConstraint extends Constraint {

  /**
   * The error message for the constraint.
   *
   * @var string
   */
  public $message = 'Donation type violation.';

}
