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

  private $endpoint = '/falcon/donation';
  private $store;
  private $variation;
  private $product;
  private $appeal;

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
      'field_donation_type' => 'single_donation',
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
   * Successful donation.
   *
   * @param \ApiTester $I
   */
  public function donationSuccess(\ApiTester $I) {
    $I->amGoingTo('Post correct order to Donation API endpoint.');
    $I->haveHttpHeader('Content-Type', 'application/json');
    $I->sendPOST($this->endpoint, [
      'order' => [
        'field_appeal' => $this->appeal->id(),
      ],
      'payment' => [
        'amount' => 15,
		    'currency_code' => 'EUR',
      ],
      'donation_type' => 'single_donation',
	    'profile' => [
        'email'  => 'test@test.com',
        'field_first_name' => 'First',
	      'field_last_name' => 'Last',
        'field_phone' => '88001234567',
        'field_contact_email' => 1,
        'field_contact_phone' => 0,
        'field_contact_post' => 0,
        'field_contact_sms' => 0,
      ],
    ]);
    $I->expectTo('See successful response.');
    $I->seeResponseCodeIs(HttpCode::OK);
  }

  /**
   * Failed donation.
   *
   * @param \ApiTester $I
   */
  public function donationFailure(\ApiTester $I) {
    $I->amGoingTo('Post incorrect order to Donation API endpoint.');
    $I->haveHttpHeader('Content-Type', 'application/json');
    $I->sendPOST($this->endpoint, [
      'order' => [
        'field_appeal' => $this->appeal->id(),
      ],
      'payment' => [
        'amount' => 5,
        'currency_code' => 'EUR',
      ],
      'donation_type' => 'single_donation',
      'profile' => [
        'email'  => 'test@test.com',
        'field_first_name' => 'First',
        'field_last_name' => 'Last',
        'field_phone' => '88001234567',
        'field_contact_email' => 1,
        'field_contact_phone' => 0,
        'field_contact_post' => 0,
        'field_contact_sms' => 0,
      ],
    ]);
    $I->expectTo('See failure response.');
    $I->seeResponseCodeIs(HttpCode::INTERNAL_SERVER_ERROR);
  }

}