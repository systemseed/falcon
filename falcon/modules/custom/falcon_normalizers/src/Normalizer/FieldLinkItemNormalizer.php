<?php

namespace Drupal\falcon_normalizers\Normalizer;

use Drupal\Core\Url;
use Drupal\serialization\Normalizer\NormalizerBase;

/**
 * Converts Drupal link into frontend-friendly structure.
 */
class FieldLinkItemNormalizer extends NormalizerBase {

  /**
   * {@inheritdoc}
   */
  protected $supportedInterfaceOrClass = 'Drupal\link\LinkItemInterface';

  /**
   * {@inheritdoc}
   */
  protected function checkFormat($format = NULL) {
    // We support only json_recursive format at the moment.
    return $format == 'json_recursive';
  }

  /**
   * {@inheritdoc}
   */
  public function normalize($field_item, $format = NULL, array $context = []) {
    /* @var \Drupal\link\Plugin\Field\FieldType\LinkItem $field_item */

    if ($field_item->isEmpty()) {
      return [];
    }

    return [
      'label' => $field_item->title,
      'url' => $this->serializer->normalize(Url::fromUri($field_item->uri), $format, $context),
    ];
  }

}
