<?php

namespace Drupal\rest_entity_recursive\Normalizer;

use Drupal\serialization\Normalizer\EntityReferenceFieldItemNormalizer;

/**
 * Class ReferenceItemNormalizer.
 *
 * @package Drupal\rest_entity_recursive\Normalizer
 */
class ReferenceItemNormalizer extends EntityReferenceFieldItemNormalizer {

  /**
   * The format that the Normalizer can handle.
   *
   * @var array
   */
  protected $format = ['json_recursive'];

  /**
   * {@inheritdoc}
   */
  public function normalize($field_item, $format = NULL, array $context = []) {

    // Check current depth. Not include entity if it is max depth.
    if ($context['current_depth'] === $context['max_depth']) {
      return parent::normalize($field_item, $format, $context);
    }
    // Increase current depth.
    $context['current_depth']++;

    $entity = $field_item->get('entity')->getValue();

    return $this->serializer->normalize($entity, $format, $context);
  }

}