<?php

namespace Drupal\falcon_normalizers\Normalizer;

use Drupal\rest_entity_recursive\Normalizer\ContentEntityNormalizer;

/**
 * Falcon specific optimisations for node normalizer.
 */
class NodeNormalizer extends ContentEntityNormalizer {

  protected $supportedInterfaceOrClass = 'Drupal\node\NodeInterface';

  /**
   * {@inheritdoc}
   */
  public function normalize($entity, $format = NULL, array $context = []) {
    return $this->normalizeFields($entity, $format, $context);
  }

  /**
   * Helper to normalize given list of fields only.
   */
  public function normalizeFields($entity, $format, $context) {
    /* @var \Drupal\node\Entity\Node $entity */
    $defaults = [
      'created' => [['value' => $entity->getCreatedTime()]],
      'updated' => [['value' => $entity->getChangedTime()]],
      'url' => $this->serializer->normalize($entity->toUrl(), $format, $context),
    ];

    $normalized = parent::normalize($entity, $format, $context);

    return $defaults + $normalized;
  }

}
