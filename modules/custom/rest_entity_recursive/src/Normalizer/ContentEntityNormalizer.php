<?php

namespace Drupal\rest_entity_recursive\Normalizer;

use Drupal\Core\TypedData\TypedDataInternalPropertiesHelper;
use Drupal\serialization\Normalizer\NormalizerBase;

/**
 * Converts the Drupal entity object structure to a JSON array structure.
 */
class ContentEntityNormalizer extends NormalizerBase {

  /**
   * The interface or class that this Normalizer supports.
   *
   * @var string
   */
  protected $supportedInterfaceOrClass = 'Drupal\Core\Entity\ContentEntityInterface';

  /**
   * The format that the Normalizer can handle.
   *
   * @var array
   */
  protected $format = ['json_recursive'];

  /**
   * Default max depth.
   *
   * @var int
   */
  protected $defaultMaxDepth = 10;

  /**
   * {@inheritdoc}
   */
  public function normalize($entity, $format = NULL, array $context = []) {
    // Create the array of normalized fields.
    $normalized = [];

    // If it is root entity set current_depth and root_entity in context.
    if (!array_key_exists('current_depth', $context)) {
      $context['current_depth'] = 0;

      // Get max_depth from request.
      $requestMaxDepth = $context['request']->query->get('max_depth');

      // Set max_depth in context.
      if ($requestMaxDepth === "0") {
        $context['max_depth'] = 0;
      }
      elseif ((int) $requestMaxDepth > 0) {
        $context['max_depth'] = (int) $requestMaxDepth;
      }
      else {
        $context['max_depth'] = $this->defaultMaxDepth;
      }

      // Set root entity in context.
      $context['root_parent_entity'] = ['id' => $entity->id(), 'type' => $entity->getEntityTypeId()];
    }

    $field_items = TypedDataInternalPropertiesHelper::getNonInternalProperties($entity->getTypedData());
    foreach ($field_items as $field) {
      // Continue if the current user does not have access to view this field.
      if (!$field->access('view', NULL)) {
        continue;
      }
      $normalized[$field->getName()] = $this->serializer->normalize($field, $format, $context);
    }

    return $normalized;
  }

}
