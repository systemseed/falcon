<?php

namespace commerce_decoupled_checkout;

use Codeception\Util\HttpCode;
use Stripe\Stripe;
use Stripe\Token as StripeToken;

/**
 * Class StripeCest.
 *
 * Tests Stripe payments through API.
 *
 * This is a demo test which does not get executed, because
 * it requires secret key to be filled in order to work.
 * However, if you want to test Stripe payments you can simply
 * create a new test which inherits this one and override
 * $this->stripeSecretKey & $this->post['payment']['gateway'] variables
 * with the actual values from your project.
 *
 * @package Commerce Decoupled Checkout
 */
class StripeCest {

  /**
   * @var object
   */
  protected $appeal;

  /**
   * @var string
   */
  protected $stripeSecretKey = '';

  /**
   * @var array
   */
  protected $post = [
    'order' => [
      'type' => 'donation',
      'field_appeal' => '',
      'order_items' => [
        [
          'type' => 'donation',
          'unit_price' => [
            'number' => 151,
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
      'gateway' => 'stripe_test',
      'type' => 'credit_card',
      'details' => [
        // Gets populated in ::stripePaymentSuccess().
        'stripe_token' => '',
      ],
    ],
  ];

  /**
   * Returns Stripe token for payment.
   *
   * @return string
   */
  protected function getStipeToken() {

    // Gifts Stripe token.
    Stripe::setApiKey($this->stripeSecretKey);

    $token = StripeToken::create([
      'card' => [
        'number' => '4242424242424242',
        'exp_month' => 4,
        'exp_year' => 2022,
        'cvc' => '123',
      ],
    ]);

    return $token->id;
  }

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
   * Failed donation due to missing payment gateway.
   *
   * @param \ApiTester $I
   * @group demo
   * @skip
   */
  public function stripePaymentSuccess(\ApiTester $I) {
    $I->amGoingTo('Post correct Stripe payment with fake order.');
    $I->haveHttpHeader('Content-Type', 'application/json');

    $post = $this->post;
    $post['order']['field_appeal'] = $this->appeal->id();
    $post['payment']['details']['stripe_token'] = $this->getStipeToken();

    $I->sendPOST('/commerce/order/create', $post);

    $I->expectTo('See successful order creation response.');
    $I->seeResponseCodeIs(HttpCode::CREATED);
  }
}
