<?php

namespace Drupal\falcon_donation\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * The validator.
 */
class UnpublishedAppealConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($entity, Constraint $constraint) {
    /** @var \Drupal\Core\Entity\EntityInterface $entity */
    if ($entity->bundle() != 'donation') {
      return;
    }

    /** @var \Drupal\node\NodeInterface $appeal */
    $appeal = $entity->field_appeal->entity;
    if (!$appeal->isPublished()) {
      $this->context->addViolation($constraint->message);
    }
  }

}
