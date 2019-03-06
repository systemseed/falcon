<?php

namespace Drupal\rest_entity_recursive\Encoder;

use Drupal\serialization\Encoder\JsonEncoder;

/**
 * Encodes data in JSON with includes entities.
 *
 * Simply respond to json_recursive format requests using the JSON encoder.
 */
class JsonRecursiveEncoder extends JsonEncoder {

  /**
   * The formats that this Encoder supports.
   *
   * @var string
   */
  protected static $format = ['json_recursive'];

}
