<?php

namespace Drupal\falcon_normalizers\Normalizer;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Url;
use Drupal\serialization\Normalizer\NormalizerBase;

/**
 * Exposes URL object as a url with entity metadata.
 */
class UrlNormalizer extends NormalizerBase {

  /**
   * The interface or class that this Normalizer supports.
   *
   * @var string
   */
  protected $supportedInterfaceOrClass = Url::class;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new UrlNormalizer.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function normalize($urlObject, $format = NULL, array $context = []) {
    /* @var \Drupal\Core\Url $urlObject */
    $normalized = [
      'url' => $urlObject->toString(),
      'is_external' => $urlObject->isExternal(),
      'entity_type' => NULL,
      'entity_bundle' => NULL,
    ];

    $query_sanitized = [];
    // Drop all parameters started with _. They are for internal use only.
    if ($query = $urlObject->getOption('query')) {
      foreach ($query as $param_name => $param_value) {
        if (strpos($param_name, '_') !== 0) {
          $query_sanitized[$param_name] = $param_value;
        }
        else {
          // Expose special parameters as link properties.
          $normalized[ltrim($param_name, '_')] = $param_value;
        }
      }
      $urlObject->setOption('query', $query_sanitized);
    }

    if ($urlObject->isExternal()) {
      // TODO: attempt to process external URLs if they use frontend app domain.
      return $normalized;
    }

    if (!$urlObject->isRouted()) {
      // The path is not managed by Drupal. Skipping.
      return $normalized;
    }

    if (!preg_match('/^entity\.(\w+)\.canonical$/', $urlObject->getRouteName(), $matches)) {
      return $normalized;
    }

    $normalized['entity_type'] = $matches[1];
    $entity_id = $urlObject->getRouteParameters()[$normalized['entity_type']];

    if ($urlObject->getOption('entity')) {
      $normalized['entity_bundle'] = $urlObject->getOption('entity')->bundle();
    }
    else {
      // Entity was not found on URL object level. Try to load it.
      try {
        $entity = $this->entityTypeManager->getStorage($normalized['entity_type'])->load($entity_id);
        if ($entity) {
          $normalized['entity_bundle'] = $entity->bundle();
        }
      }
      catch (\Exception $e) {
        watchdog_exception('falcon_normalizers', $e);
      }
    }

    // URL object may have been changed in this function.
    // Make sure the changes are represented in url property.
    $normalized['url'] = $urlObject->toString();

    return $normalized;
  }

}
