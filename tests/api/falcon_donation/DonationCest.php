<?php

namespace falcon_commerce;

use Codeception\Util\HttpCode;
use Drupal\commerce_price\Price;

/**
 * Class DonationCest.
 *
 * Donation API tests.
 *
 * @package Falcon Commerce
 */
class DonationCest {

  private $store;
  private $variation;
  private $product;
  private $appeal;

  private $post = [
    'donation_type' => 'single_donation',
    'order' => [
      'field_appeal' => '',
    ],
    'payment' => [
      'amount' => 15,
      'currency_code' => 'EUR',
      'gateway' => 'example_test',
      'method' => [
        'type' => 'credit_card',
        'options' => [
          'type' => 'visa',
          'number' => '4111111111111111',
          'expiration' => [
            'month' => '01',
            'year' => '2022',
          ],
        ],
      ],
    ],
    'profile' => [
      'email'  => 'test@systemseed.com',
      'field_first_name' => 'Generous',
      'field_last_name' => 'Donor',
      'field_phone' => '88001234567',
      'field_contact_email' => 1,
      'field_contact_phone' => 0,
      'field_contact_post' => 0,
      'field_contact_sms' => 0,
      'address' => [
        'country_code' => 'US',
        'address_line1' => '1098 Alta Ave',
        'locality' => 'Mountain View',
        'administrative_area' => 'CA',
        'postal_code' => '94043'
      ],
    ],
  ];

  const ENDPOINT = '/falcon/donation';

  public function _before(\ApiTester $I) {
    $entity_type_manager = \Drupal::entityTypeManager();

    // Create default Commerce Store.
    $address = [
      'country_code' => 'US',
      'address_line1' => '8 The Green',
      'address_line2' => 'Ste A',
      'locality' => 'Dover',
      'administrative_area' => 'DE',
      'postal_code' => '19901',
    ];

    $store = $entity_type_manager->getStorage('commerce_store')->create([
      'type' => 'online',
      'uid' => 1,
      'name' => 'General Store',
      'mail' => 'info@systemseed.com',
      'address' => $address,
      'default_currency' => 'EUR',
      'billing_countries' => [],
    ]);
    $store->save();
    $this->store = $store;

    // Create product variation.
    $price = new Price('0', 'EUR');
    $variation = $entity_type_manager->getStorage('commerce_product_variation')->create([
      'type' => 'donation',
      'title' => 'Donation',
      'sku' => 'donation',
      'status' => 1,
      'price' => $price,
    ]);
    $variation->save();
    $this->variation = $variation;

    // Create product.
    $product = $entity_type_manager->getStorage('commerce_product')->create([
      'uid' => 1,
      'type' => 'donation',
      'title' => 'Donation',
      'stores' => [$store],
      'variations' => [$variation],
    ]);
    $product->save();
    $this->product = $product;

    // Create appeal.
    $appeal = $entity_type_manager->getStorage('node')->create([
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
    $appeal->save();
    $this->appeal = $appeal;
  }

  public function _after(\ApiTester $I) {
    $entity_type_manager = \Drupal::entityTypeManager();

    $entity_type_manager->getStorage('commerce_store')->delete([$this->store]);
    $entity_type_manager->getStorage('commerce_product_variation')->delete([$this->variation]);
    $entity_type_manager->getStorage('commerce_product')->delete([$this->product]);
    $entity_type_manager->getStorage('node')->delete([$this->appeal]);
  }

  /**
   * Successful single donation via Example Test payment gateway.
   *
   * @param \ApiTester $I
   */
  public function donationSingleExampleSuccess(\ApiTester $I) {
    $I->amGoingTo('Post correct order to Donation API endpoint.');
    $I->haveHttpHeader('Content-Type', 'application/json');

    $post = $this->post;
    $post['order']['field_appeal'] = $this->appeal->id();
    $I->sendPOST(self::ENDPOINT, $post);

    $I->expectTo('See successful response.');
    $I->seeResponseCodeIs(HttpCode::OK);
  }

  /**
   * Successful recurring donation via Direct Debit Test payment gateway - SEPA.
   *
   * @param \ApiTester $I
   */
  public function donationRecurringDirectDebitSEPASuccess(\ApiTester $I) {
    $I->amGoingTo('Post correct order to Donation API endpoint.');
    $I->haveHttpHeader('Content-Type', 'application/json');

    $post = $this->post;
    $post['order']['field_appeal'] = $this->appeal->id();
    $post['donation_type'] = 'recurring_donation';
    $post['payment']['gateway'] = 'direct_debit_test';
    $post['payment']['method'] = [
      'type' => 'direct_debit_sepa',
      'options' => [
        'account_name' => 'Generous Donor',
        'swift' => 'BOFIIE2D',
        'iban' => 'DE89 3704 0044 0532 0130 00',
        'debit_date' => 2,
        'accept_direct_debits' => 1,
        'one_signatory' => 1,
      ],
    ];
    $I->sendPOST(self::ENDPOINT, $post);

    $I->expectTo('See successful response.');
    $I->seeResponseCodeIs(HttpCode::OK);
  }

  /**
   * Successful recurring donation via Direct Debit Test payment gateway - UK.
   *
   * @param \ApiTester $I
   */
  public function donationRecurringDirectDebitUKSuccess(\ApiTester $I) {
    $I->amGoingTo('Post correct order to Donation API endpoint.');
    $I->haveHttpHeader('Content-Type', 'application/json');

    $post = $this->post;
    $post['order']['field_appeal'] = $this->appeal->id();
    $post['donation_type'] = 'recurring_donation';
    $post['payment']['gateway'] = 'direct_debit_test';
    $post['payment']['method'] = [
      'type' => 'direct_debit_uk',
      'options' => [
        'account_name' => 'Generous Donor',
        'sort_code' => '123456',
        'account_number' => '12345678',
        'debit_date' => 2,
        'accept_direct_debits' => 1,
        'one_signatory' => 1,
      ],
    ];
    $I->sendPOST(self::ENDPOINT, $post);

    $I->expectTo('See successful response.');
    $I->seeResponseCodeIs(HttpCode::OK);
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
    $post['payment']['amount'] = 5;
    $I->sendPOST(self::ENDPOINT, $post);

    $I->expectTo('See failure response.');
    $I->seeResponseCodeIs(HttpCode::NOT_ACCEPTABLE);
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
    $I->sendPOST(self::ENDPOINT, $post);

    $I->expectTo('See failure response.');
    $I->seeResponseCodeIs(HttpCode::NOT_ACCEPTABLE);
  }

}
