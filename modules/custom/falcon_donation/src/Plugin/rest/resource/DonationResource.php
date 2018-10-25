<?php

namespace Drupal\falcon_donation\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;

/**
 * TODO: Legacy & should be removed in the future releases.
 *
 * @RestResource(
 *   id = "donation",
 *   label = @Translation("Falcon Commerce Donation"),
 *   uri_paths = {
 *     "create" = "/falcon/donation"
 *   }
 * )
 */
class DonationResource extends ResourceBase {

}
