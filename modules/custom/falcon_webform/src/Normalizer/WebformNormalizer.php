<?php

namespace Drupal\falcon_webform\Normalizer;

use Drupal\webform\WebformInterface;
use Drupal\rest_entity_recursive\Normalizer\ContentEntityNormalizer;

/**
 * Customized normalizer for entity type 'webform'.
 */
class WebformNormalizer extends ContentEntityNormalizer {

  /**
   * {@inheritdoc}
   */
  protected $supportedInterfaceOrClass = WebformInterface::class;

  /**
   * {@inheritdoc}
   */
  public function normalize($webform, $format = NULL, array $context = []) {
    // Add the current webform entity as a cacheable dependency to make Drupal
    // flush the cache when the webform entity gets updated.
    $this->addCacheableDependency($context, $webform);

    /* @see: \Drupal\webform\WebformEntityAccessControlHandler::checkAccess() */
    if (!$webform->access('submission_page') || !$webform->isOpen()) {
      // Do we need logger here??
      return [];
    }

    $normalized = [
      'entity_type' => [['value' => $webform->getEntityTypeId()]],
      'entity_bundle' => [['value' => $webform->bundle()]],
      'id' => $webform->getOriginalId(),
      'title' => $webform->label(),
      'description' => $webform->getDescription(),
      'success_message' => $webform->getSetting('confirmation_message'),
    ];

    // Get elements for build form.
    $fields = $webform->getElementsDecoded();

    // Normalize elements.
    $normalizedFields = [];
    foreach ($fields as $key => $field) {
      $normalizedFields[] = array_merge($this->getNormalizedField($field), ['field_id' => $key]);
    }

    $normalized['fields'] = $normalizedFields;

    return $normalized;
  }

  /**
   * Normalization webform field.
   *
   * @param array $values
   *   Array of data.
   *
   * @return array
   *   Array of normalized values.
   */
  private function getNormalizedField(array $values) {
    $result = [];
    $children = [];
    foreach ($values as $key => $value) {
      // Remove # from key.
      if ($key[0] === "#") {
        $result[substr($key, 1)] = $value;
      }
      // Add child if key has not # (Fieldset case).
      else {
        if (is_array($value)) {
          $value = $this->getNormalizedField($value);
        }
        $children[] = array_merge($value, ['field_id' => $key]);
      }
    }
    if (!empty($children)) {
      $result['children'] = $children;
    }

    return $result;
  }

}
