<?php

namespace falcon_commerce;

use Codeception\Util\HttpCode;
use Drupal\commerce_price\Price;

/**
 * Class EcardGiftCest.
 *
 * Gift API tests.
 *
 * @package Falcon Commerce
 */
class EcardGiftCest {

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
  private $entityTypeManager;

  /**
   * @var
   */
  private $orderId;

  /**
   * @var
   */
  private $ecardId;

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
          'field_card_delivery' => 'email',
          'field_card' => [
            'field_subject' => 'Test ecard subject',
            'field_recipient_email' => 'test+suite@systemseed.com',
            'field_message' => '<html><body><div style="color:red">Ecard gift test mail</div></body></html>'
          ],
        ],
        [
          'type' => 'gift',
          'purchased_entity' => [
            'sku' => 'test gift',
          ]
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
    $this->orderId = NULL;
    $this->ecardId = NULL;
    $this->entityTypeManager = \Drupal::entityTypeManager();

    $stores = $this->entityTypeManager->getStorage('commerce_store')->loadByProperties(['type' => 'online']);

    // Create product variation gift.
    $this->variation = $this->entityTypeManager->getStorage('commerce_product_variation')->create([
      'type' => 'gift',
      'title' => 'Test gift with ecard',
      'sku' => 'test gift',
      'status' => 1,
      'price' => new Price('10', 'USD'),
    ]);
    $this->variation->save();

    // Create product gift.
    $this->product = $this->entityTypeManager->getStorage('commerce_product')->create([
      'uid' => 1,
      'type' => 'gift',
      'title' => 'Test gift with ecard',
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

    if (!empty($this->ecardId)) {
      $this->entityTypeManager->getStorage('gift_cards')
        ->delete([$this->entityTypeManager->getStorage('gift_cards')->load($this->ecardId)]);
    }
  }

  /**
   * Successful created ecard after created order.
   *
   * @param \ApiTester $I
   * @group additional
   */
  public function EcardGiftSingleExampleSuccess(\ApiTester $I) {
    $I->amGoingTo('Post order with gift with ecard.');
    $I->haveHttpHeader('Content-Type', 'application/json');

    $I->sendPOST('/commerce/order/create', $this->post);

    $I->expectTo('See successful response.');
    $I->seeResponseCodeIs(HttpCode::CREATED);

    $this->orderId = $I->grabDataFromResponseByJsonPath("$.order_id[0].value")[0];

    // Get order_id from response.
    $orderItemID = $I->grabDataFromResponseByJsonPath("$..order_items[0].target_id")[0];
    $orderItem = $this->entityTypeManager->getStorage('commerce_order_item')->load($orderItemID);

    // Order item exist.
    $I->assertNotEmpty($orderItem, 'Order item');

    $deliveryType = $orderItem->get('field_card_delivery')->getValue()[0]['value'];

    // Order item has email delivery type.
    $I->assertEquals('email', $deliveryType);

    $fieldCard = $orderItem->get('field_card')->getValue();

    // Order item has reference on ecard.
    $I->assertNotEmpty($fieldCard, 'field card');

    $this->ecardId = $fieldCard[0]['target_id'];

    $ecard = $this->entityTypeManager->getStorage('gift_cards')->load($this->ecardId);

    // Ecard is exist.
    $I->assertNotEmpty($ecard, 'Ecard');

    // Ecard status is sent.
    $I->assertTrue(!!$ecard->get('field_status')->getValue()[0]['value'], 'Ecard status');
  }

}
