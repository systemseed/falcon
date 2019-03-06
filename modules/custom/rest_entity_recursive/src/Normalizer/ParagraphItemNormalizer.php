<?php

namespace Drupal\rest_entity_recursive\Normalizer;

use Drupal\paragraphs\ParagraphInterface;

/**
 * Class ParagraphItemNormalizer.
 *
 * Normalizer adds paragraph type to response.
 *
 * @package Drupal\rest_entity_recursive\Normalizer
 */
class ParagraphItemNormalizer extends ContentEntityNormalizer {

  /**
   * The interface or class that this Normalizer supports.
   *
   * @var string
   */
  protected $supportedInterfaceOrClass = ParagraphInterface::class;

  /**
   * {@inheritdoc}
   */
  public function normalize($entity, $format = NULL, array $context = []) {
    $normalized_values = parent::normalize($entity, $format, $context);
    $normalized_values['paragraph_type'] = $entity->getType();
    return $normalized_values;
  }

}
