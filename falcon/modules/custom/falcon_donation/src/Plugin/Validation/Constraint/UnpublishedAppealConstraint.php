<?php

namespace Drupal\falcon_donation\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * The constraint object.
 *
 * @Constraint(
 *   id = "falcon_donation__unpublished_appeal",
 *   label = @Translation("Unpublished appeal", context = "Validation")
 * )
 */
class UnpublishedAppealConstraint extends Constraint {

  /**
   * The error message for the constraint.
   *
   * @var string
   */
  public $message = 'Unpublished appeal violation.';

}
