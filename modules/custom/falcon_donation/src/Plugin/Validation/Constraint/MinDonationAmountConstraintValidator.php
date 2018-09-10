<?php

namespace Drupal\falcon_donation\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * The validator.
 */
class MinDonationAmountConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($entity, Constraint $constraint) {
    /** @var \Drupal\Core\Entity\EntityInterface $entity */
    if ($entity->bundle() != 'donation') {
      return;
    }

    $min_donation_amount = $entity->field_appeal->entity->field_donation_min_amount->value;
    /** @var \Drupal\commerce_order\Entity\OrderItemInterface $order_item */
    $order_item = $entity->order_items->entity;
    if ($order_item->getUnitPrice()->getNumber() < $min_donation_amount) {
      $this->context->addViolation($constraint->message);
    }
  }

}
