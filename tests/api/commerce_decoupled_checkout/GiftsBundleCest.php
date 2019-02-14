<?php

namespace falcon_commerce;

use Codeception\Util\HttpCode;
use Drupal\commerce_price\Price;

/**
 * Class GiftBundleCest.
 *
 * Gift API tests.
 *
 * @package Falcon Commerce
 */
class GiftBundleCest {

  private $variation;

  private $product;

  /**
   * @var array
   */
  private $post = [
    'order' => [
      'type' => 'gift',
      'order_items' => [
        [
          'type' => 'gifts_bundle',
          'purchased_entity' => [
            'sku' => 'test bundle',
          ],
        ],
      ],
    ],
    'profile' => [
      'field_phone' => '88001234567',
      'field_contact_email' => 'allowed',
      'field_contact_phone' => 'allowed',
      'field_contact_sms' => 'denied',
      'address' => [
        'given_name' => 'Generous',
        'family_name' => 'Donor',
        'country_code' => 'US',
        'address_line1' => '1098 Alta Ave',
        'locality' => 'Mountain View',
        'administrative_area' => 'CA',
        'postal_code' => '94043',
      ],
    ],
    'user' => [
      'mail' => 'test+suite@systemseed.com',
    ]
  ];

  /**
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function _before() {
    $entity_type_manager = \Drupal::entityTypeManager();

    $stores = $entity_type_manager->getStorage('commerce_store')->loadByProperties(['type' => 'online']);

    // Create product variation gifts bundle.
    $this->variation = $entity_type_manager->getStorage('commerce_product_variation')->create([
      'type' => 'gifts_bundle',
      'title' => 'Test gift',
      'sku' => 'test bundle',
      'status' => 1,
      'price' => new Price('10', 'USD'),
    ]);
    $this->variation->save();

    // Create product gifts bundle.
    $this->product = $entity_type_manager->getStorage('commerce_product')->create([
      'uid' => 1,
      'type' => 'gifts_bundle',
      'title' => 'Test gifts bundle',
      'stores' => $stores,
      'variations' => [$this->variation],
    ]);
    $this->product->save();
  }

  /**
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function _after() {
    $entity_type_manager = \Drupal::entityTypeManager();

    $entity_type_manager->getStorage('commerce_product_variation')->delete([$this->variation]);
    $entity_type_manager->getStorage('commerce_product')->delete([$this->product]);
  }

  /**
   * Successful created order with single gifts bundle.
   *
   * @param \ApiTester $I
   * @group additional
   */
  public function giftsBundleSingleExampleSuccess(\ApiTester $I) {
    $I->amGoingTo('Post correct order with gifts bundle to Commerce Create REST endpoint.');
    $I->haveHttpHeader('Content-Type', 'application/json');

    $post = $this->post;
    $I->sendPOST('/commerce/order/create', $post);

    $I->expectTo('See successful response.');
    $I->seeResponseCodeIs(HttpCode::CREATED);
  }

}
