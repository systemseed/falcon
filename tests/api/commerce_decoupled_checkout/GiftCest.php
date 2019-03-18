<?php

namespace falcon_commerce;

use Codeception\Util\HttpCode;
use Drupal\commerce_price\Price;

/**
 * Class GiftCest.
 *
 * Gift API tests.
 *
 * @package Falcon Commerce
 */
class GiftCest {

  /**
   * @var
   */
  private $entityTypeManager;

  /**
   * @var
   */
  private $variation;

  /**
   * @var
   */
  private $product;

  /**
   * @var
   */
  private $orderId;

  /**
   * @var array
   */
  private $post = [
    'order' => [
      'type' => 'gift',
      'order_items' => [
        [
          'type' => 'gift',
          'purchased_entity' => [
            'sku' => 'test gift',
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
    $this->orderId = NULL;
    $this->entityTypeManager = \Drupal::entityTypeManager();

    $stores = $this->entityTypeManager->getStorage('commerce_store')->loadByProperties(['type' => 'online']);

    // Create product variation gift.
    $this->variation = $this->entityTypeManager->getStorage('commerce_product_variation')->create([
      'type' => 'gift',
      'title' => 'Test gift',
      'sku' => 'test gift',
      'status' => 1,
      'price' => new Price('10', 'USD'),
    ]);
    $this->variation->save();

    // Create product gift.
    $this->product = $this->entityTypeManager->getStorage('commerce_product')->create([
      'uid' => 1,
      'type' => 'gift',
      'title' => 'Test gift',
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
    $this->entityTypeManager->getStorage('commerce_product_variation')->delete([$this->variation]);
    $this->entityTypeManager->getStorage('commerce_product')->delete([$this->product]);

    if (!empty($this->orderId)) {
      $this->entityTypeManager->getStorage('commerce_order')
        ->delete([$this->entityTypeManager->getStorage('commerce_order')->load($this->orderId)]);
    }
  }

  /**
   * Successful created order with single gift.
   *
   * @param \ApiTester $I
   * @group additional
   */
  public function giftSingleExampleSuccess(\ApiTester $I) {
    $I->amGoingTo('Post correct order with gift to Commerce Create REST endpoint.');
    $I->haveHttpHeader('Content-Type', 'application/json');

    $post = $this->post;
    $I->sendPOST('/commerce/order/create', $post);

    $I->expectTo('See successful response.');
    $I->seeResponseCodeIs(HttpCode::CREATED);

    $this->orderId =$I->grabDataFromResponseByJsonPath("$.order_id[0].value")[0];
  }

}
