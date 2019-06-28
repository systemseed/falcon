<?php

namespace Drupal\falcon_donation\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * The validator.
 */
class ZeroDonationAmountConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($entity, Constraint $constraint) {
    /** @var \Drupal\Core\Entity\EntityInterface $entity */
    if ($entity->bundle() != 'donation') {
      return;
    }

    if ($entity->getUnitPrice()->getNumber() == 0) {
      $this->context->addViolation($constraint->message);
    }
  }

}
