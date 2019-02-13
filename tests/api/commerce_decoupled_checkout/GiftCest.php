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
    ],
    'payment' => [
      'gateway' => 'example_test',
      'type' => 'credit_card',
      'details' => [
        'type' => 'visa',
        'number' => '4111111111111111',
        'expiration' => [
          'month' => '01',
          'year' => '2022',
        ],
      ],
    ],
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

    // Create product variation gift.
    $this->variation = $entity_type_manager->getStorage('commerce_product_variation')->create([
      'type' => 'gift',
      'title' => 'Test gift',
      'sku' => 'test gift',
      'status' => 1,
      'price' => new Price('10', 'USD'),
    ]);
    $this->variation->save();

    // Create product gift.
    $this->product = $entity_type_manager->getStorage('commerce_product')->create([
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
    $entity_type_manager = \Drupal::entityTypeManager();

    $entity_type_manager->getStorage('commerce_product_variation')->delete([$this->variation]);
    $entity_type_manager->getStorage('commerce_product')->delete([$this->product]);
  }

  /**
   * Successful single gift via Example Test payment gateway.
   *
   * @param \ApiTester $I
   * @group additional
   */
  public function giftSingleExampleSuccess(\ApiTester $I) {
    $I->amGoingTo('Post correct order Gift to Commerce Create REST endpoint.');
    $I->haveHttpHeader('Content-Type', 'application/json');

    $post = $this->post;
    $I->sendPOST('/commerce/order/create', $post);

    $I->expectTo('See successful response.');
    $I->seeResponseCodeIs(HttpCode::CREATED);
  }

  /**
   * Failed gift due to missing payment gateway.
   *
   * @param \ApiTester $I
   * @group additional
   */
  public function giftGatewayFailure(\ApiTester $I) {
    $I->amGoingTo('Post incorrect order to Gift API endpoint.');
    $I->haveHttpHeader('Content-Type', 'application/json');

    $post = $this->post;
    $post['payment']['gateway'] = 'missing';
    $I->sendPOST('/commerce/order/create', $post);

    $I->expectTo('See failure response.');
    $I->seeResponseCodeIs(HttpCode::NOT_ACCEPTABLE);
  }

}
