<?php

namespace falcon_commerce;

use Codeception\Util\HttpCode;

/**
 * Class DonationCest.
 *
 * Donation API tests.
 *
 * @package Falcon Commerce
 */
class DonationCest {

  private $appeal;

  /**
   * @var array
   */
  private $post = [
    'order' => [
      'type' => 'donation',
      'field_appeal' => '',
      'order_items' => [
        [
          'type' => 'donation',
          'unit_price' => [
            'number' => 15,
            'currency_code' => 'EUR',
          ],
          'purchased_entity' => [
            'sku' => 'donation',
          ],
          'field_donation_type' => 'single_donation',
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

    // Create appeal.
    $this->appeal = $entity_type_manager->getStorage('node')->create([
      'uid' => 1,
      'type' => 'appeal',
      'title' => 'Test appeal',
      'status' => 1,
      'field_donation_min_amount' => 10,
      'field_donation_type' => ['single_donation', 'recurring_donation'],
      'field_thankyou_page_title' => 'Thank you page title',
      'field_thankyou_email_subject' => 'Thank you email subject',
      'field_thankyou_email_body' => 'Thank you email body',
    ]);
    $this->appeal->save();
  }

  /**
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function _after() {
    $entity_type_manager = \Drupal::entityTypeManager();

    $entity_type_manager->getStorage('node')->delete([$this->appeal]);
  }

  /**
   * Successful single donation via Example Test payment gateway.
   *
   * @param \ApiTester $I
   */
  public function donationSingleExampleSuccess(\ApiTester $I) {
    $I->amGoingTo('Post correct order to Commerce Create REST endpoint.');
    $I->haveHttpHeader('Content-Type', 'application/json');

    $post = $this->post;
    $post['order']['field_appeal'] = $this->appeal->id();
    $I->sendPOST('/commerce/order/create', $post);

    /** @var \Drupal\commerce_store\StoreStorageInterface $commerce_store */
    //$commerce_store = \Drupal::entityTypeManager()
    //  ->getStorage('commerce_store');

    //Debug::debug($commerce_store->loadDefault());

    $I->expectTo('See successful response.');
    $I->seeResponseCodeIs(HttpCode::CREATED);
  }

  /**
   * Successful recurring donation via Direct Debit Test payment gateway - SEPA.
   *
   * @param \ApiTester $I
   */
  public function donationRecurringDirectDebitSEPASuccess(\ApiTester $I) {
    $I->amGoingTo('Post correct order to Commerce Create REST endpoint.');
    $I->haveHttpHeader('Content-Type', 'application/json');

    $post = $this->post;
    $post['order']['field_appeal'] = $this->appeal->id();
    $post['order']['order_items'][0]['field_donation_type'] = 'recurring_donation';
    $post['payment']['gateway'] = 'direct_debit_test';
    $post['payment']['type'] = 'direct_debit_sepa';
    $post['payment']['details'] = [
        'account_name' => 'Generous Donor',
        'swift' => 'BOFIIE2D',
        'iban' => 'DE89 3704 0044 0532 0130 00',
        'debit_date' => 2,
        'accept_direct_debits' => 1,
        'one_signatory' => 1,
    ];
    $I->sendPOST('/commerce/order/create', $post);

    $I->expectTo('See successful response.');
    $I->seeResponseCodeIs(HttpCode::CREATED);
  }

  /**
   * Successful recurring donation via Direct Debit Test payment gateway - UK.
   *
   * @param \ApiTester $I
   */
  public function donationRecurringDirectDebitUKSuccess(\ApiTester $I) {
    $I->amGoingTo('Post correct order to Commerce Create REST endpoint.');
    $I->haveHttpHeader('Content-Type', 'application/json');

    $post = $this->post;
    $post['order']['field_appeal'] = $this->appeal->id();
    $post['order']['order_items'][0]['field_donation_type'] = 'recurring_donation';
    $post['payment']['gateway'] = 'direct_debit_test';
    $post['payment']['type'] = 'direct_debit_uk';
    $post['payment']['details'] = [
      'account_name' => 'Generous Donor',
      'sort_code' => '123456',
      'account_number' => '12345678',
      'debit_date' => 2,
      'accept_direct_debits' => 1,
      'one_signatory' => 1,
    ];
    $I->sendPOST('/commerce/order/create', $post);

    $I->expectTo('See successful response.');
    $I->seeResponseCodeIs(HttpCode::CREATED);
  }

  /**
   * Failed donation due to minimal donation amount restriction.
   *
   * @param \ApiTester $I
   */
  public function donationAmountFailure(\ApiTester $I) {
    $I->amGoingTo('Post incorrect order to Donation API endpoint.');
    $I->haveHttpHeader('Content-Type', 'application/json');

    $post = $this->post;
    $post['order']['field_appeal'] = $this->appeal->id();
    $post['order']['order_items'][0]['unit_price']['number'] = 5;
    $I->sendPOST('/commerce/order/create', $post);

    $I->expectTo('See failure response.');
    $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
  }

  /**
   * Failed donation due to missing payment gateway.
   *
   * @param \ApiTester $I
   */
  public function donationGatewayFailure(\ApiTester $I) {
    $I->amGoingTo('Post incorrect order to Donation API endpoint.');
    $I->haveHttpHeader('Content-Type', 'application/json');

    $post = $this->post;
    $post['order']['field_appeal'] = $this->appeal->id();
    $post['payment']['gateway'] = 'missing';
    $I->sendPOST('/commerce/order/create', $post);

    $I->expectTo('See failure response.');
    $I->seeResponseCodeIs(HttpCode::NOT_ACCEPTABLE);
  }

}
