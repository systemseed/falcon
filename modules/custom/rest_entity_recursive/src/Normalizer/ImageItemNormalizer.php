<?php

namespace Drupal\rest_entity_recursive\Normalizer;

use Drupal\consumer_image_styles\ImageStylesProviderInterface;
use Drupal\consumers\Negotiator;
use Drupal\Core\Entity\EntityInterface;
use Drupal\file\Entity\File;

/**
 * Class ImageItemNormalizer.
 *
 * Normalizer adds image styles for image.
 *
 * @package Drupal\rest_entity_recursive\Normalizer
 */
class ImageItemNormalizer extends ContentEntityNormalizer {

  /**
   * The interface or class that this Normalizer supports.
   *
   * @var string
   */
  protected $supportedInterfaceOrClass = File::class;

  /**
   * Consumer negotiator.
   *
   * @var \Drupal\consumers\Negotiator
   */
  protected $consumerNegotiator;

  /**
   * Image style provider.
   *
   * @var \Drupal\consumer_image_styles\ImageStylesProviderInterface
   */
  protected $imageStylesProvider;

  /**
   * Constructs an ImageItemNormalizer object.
   *
   * @param \Drupal\consumers\Negotiator $consumer_negotiator
   *   The consumer negotiator.
   * @param \Drupal\consumer_image_styles\ImageStylesProviderInterface $imageStylesProvider
   *   Image styles utility.
   */
  public function __construct(Negotiator $consumer_negotiator, ImageStylesProviderInterface $imageStylesProvider) {
    $this->consumerNegotiator = $consumer_negotiator;
    $this->imageStylesProvider = $imageStylesProvider;
  }

  /**
   * {@inheritdoc}
   */
  public function supportsNormalization($data, $format = NULL) {
    // It is very tricky to detect if a file entity is an image or not. This is
    // typically done using a special field type to point to this entity.
    // However we don't have access to that in here. Besides we want this to
    // apply when requesting a listing of file entities as well, not only via
    // includes. For all this we'll do string matching against the mimetype.
    return parent::supportsNormalization($data, $format) &&
      strpos($data->get('filemime')->value, 'image/') !== FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function normalize($entity, $format = NULL, array $context = []) {
    $normalized_values = parent::normalize($entity, $format, $context);
    $normalized_values['image_styles'] = $this->buildVariantValues($entity);
    return $normalized_values;
  }

  /**
   * Creates array of image styles for image.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Entity.
   * @param array $context
   *   Context.
   */
  protected function buildVariantValues(EntityInterface $entity, array $context = []) {
    $request = empty($context['request']) ? NULL : $context['request'];
    $consumer = $this->consumerNegotiator->negotiateFromRequest($request);

    // Bail-out if no consumer is found.
    if (!$consumer) {
      $access = $entity->access('view', $context['account'], TRUE);
      return $access;
    }

    // Prepare some utils.
    $uri = $entity->get('uri')->value;
    $get_image_url = function ($image_style) use ($uri) {
      return file_create_url($image_style->buildUrl($uri));
    };

    // Generate derivatives only for the found ones.
    $image_styles = $this->imageStylesProvider->loadStyles($consumer);
    $keys = array_keys($image_styles);
    $values = array_map($get_image_url, array_values($image_styles));
    $value = array_combine($keys, $values);

    return $value;
  }

}
