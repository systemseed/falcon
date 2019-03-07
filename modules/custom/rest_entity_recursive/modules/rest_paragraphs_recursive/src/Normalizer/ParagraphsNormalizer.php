<?php

namespace Drupal\rest_paragraphs_recursive\Normalizer;

use Drupal\rest_entity_recursive\Normalizer\ContentEntityNormalizer;

/**
 * Paragraphs normalizer for json_recursive format.
 */
class ParagraphsNormalizer extends ContentEntityNormalizer {

  /**
   * The interface or class that this Normalizer supports.
   *
   * @var string
   */
  protected $supportedInterfaceOrClass = 'Drupal\paragraphs\ParagraphInterface';

  protected $supportedParagraphTypes = [];

  protected $excludedFields = [
    'revision_id',
    'langcode',
    'type',
    'uid',
    'status',
    'created',
    'revision_uid',
    'default_langcode',
    'revision_translation_affected',
    'behavior_settings',
  ];

  /**
   * {@inheritdoc}
   */
  public function supportsNormalization($entity, $format = NULL) {
    if (parent::supportsNormalization($entity, $format)) {
      if (empty($this->supportedParagraphTypes)) {
        return TRUE;
      }
      if (in_array($entity->getType(), $this->supportedParagraphTypes)) {
        return TRUE;
      }
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function normalize($entity, $format = NULL, array $context = []) {

    // Ask REST Entity Recursive to exclude certain fields.
    $context['settings'][$entity->getEntityTypeId()]['exclude_fields'] = $this->excludedFields;
    $bundle = $entity->getType();
    $normalized_values = parent::normalize($entity, $format, $context);
    return $normalized_values + ['paragraph_type' => [['value' => $bundle]]];
  }

}
