<?php

namespace Drupal\falcon_donation\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * The validator.
 */
class DonationTypeConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($entity, Constraint $constraint) {
    /** @var \Drupal\Core\Entity\EntityInterface $entity */
    if ($entity->bundle() != 'donation') {
      return;
    }

    $validated = FALSE;

    /** @var \Drupal\commerce_order\Entity\OrderItemInterface $order_item */
    $order_item = $entity->order_items->entity;
    $submitted_donation_type = $order_item->field_donation_type->value;

    /** @var \Drupal\Core\Field\FieldItemListInterface $donation_types */
    $donation_types = $entity->field_appeal->entity->field_donation_type;
    /** @var \Drupal\Core\Field\FieldItemInterface $donation_type */
    foreach ($donation_types as $donation_type) {
      if ($donation_type->value === $submitted_donation_type) {
        $validated = TRUE;
      }
    }

    if (!$validated) {
      $this->context->addViolation($constraint->message);
    }
  }

}
