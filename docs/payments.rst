Payments / Checkout API
=======================

Falcon uses `Commerce Decoupled Checkout <https://www.drupal.org/project/commerce_decoupled_checkout>`_ module as an endpoint for all Commerce order creation and payments.
The REST endpoint is ``/commerce/order/create``.

You can find the link about the expected payload and additional payment related endpoints in the `module's documentation <https://cgit.drupalcode.org/commerce_decoupled_checkout/tree/src/Plugin/rest/resource/OrderCreateResource.php#n108>`_.

Below you will find more examples how those endpoints are used on Falcon for various use cases and payment gateways.

Additionally, you can look at tests inside of ``./tests/api/commerce_decoupled_checkout`` folder for real payment examples.

Supported Payment Gateways
--------------------------

Out of the box Falcon comes with support of the following payment methods:

- Paypal (disabled by default)
- Credit Cards through Stripe (disabled by default)
- Credit Cards through Realex / Global Payments (disabled by default)
- Direct Debits (enabled by default)
- Example payment (enabled by default). Just for demo purpose. Consider disabling or removing it when going live.

In fact, thanks to `Commerce Decoupled Checkout <https://www.drupal.org/project/commerce_decoupled_checkout>`_ any other
Drupal module which provides payment gateway for Drupal Commerce is supported as well.

Donations API example
---------------------

Here's the example REST Payload to submit a new donation with Example Payment method:

.. code-block:: javascript
   :linenos:

      {
        "order": {
          "type": "donation"
          "field_appeal": 1 // Appeal ID. Mandatory.
          "order_items": [
            {
              "type": "donation",
              "field_donation_type": "single_donation", // Donation type. Can be "single_donation" or "recurring_donation".
              "purchased_entity": {
                "sku": "donation"
              },
              "unit_price": {
                "number": 15,
                "currency_code": "EUR",
              }
            }
          ],
        },
        "profile": {
          "field_phone": "88001234567",
          "field_contact_email": 1,
          "field_contact_phone": 0,
          "field_contact_post": 0,
          "field_contact_sms": 0,
          "address": {
            "given_name": "John",
            "family_name": "Snow",
            "country_code": "US",
            "address_line1": "1098 Alta Ave",
            "locality": "Mountain View",
            "administrative_area": "CA",
            "postal_code": "94043"
          }
        },
        "user": {
          "mail" : "test@systemseed.com",
        },
        "payment": {
          "gateway": "example_test", // Machine name of the payment gateway added by admin.
          "type": "credit_card",
          "details": {
            "type": "visa",
            "number": "4111111111111111",
            "expiration": {
            "month": "01",
            "year": "2022"
          },
        },
      }


Paypal Payments
---------------

Requires `Commerce Paypal Express Checkout <https://github.com/systemseed/commerce_paypal_ec>`_ module to be enabled & configured (already part of Falcon).

To make Paypal payments, the first step is to send request to standard endpoint ``/commerce/order/create`` with order / user info (no payments details yet).
This request will return created Order ID - save it into a variable. The further step depends on Single vs Recurring payment.

In case of *Single Payment*, send this data to ``/commerce/payment/create/$orderID``:

.. code-block:: javascript
   :linenos:

   {
     gateway: "paypal_ec_test",
     type: "paypal_ec",
     details: {
       type: 'single',
       data: {
         transactions: [{
           description: 'Single donation.',
         }],
       },
     }
   }

This response will initialize a payment transaction and return created Payment ID - save it as well.
Next, the confirmation of the transaction should happen on the client side by a user.
See `Paypal Checkout <https://developer.paypal.com/docs/checkout/>`_ for detailed documentation of frontend implementation.

When a user confirms transaction on the frontend, send this payload to ``/commerce/payment/capture/$orderID/$paymentID``.
It should finalize the payment transaction.

In case of *Recurring Payment*, send this data to ``/commerce/payment/create/$orderID``:

.. code-block:: javascript
   :linenos:

   {
     gateway: "paypal_ec_test",
     type: "paypal_ec",
     details: {
       type: 'single',
       data: {
         billing_plan: {
           name: 'Monthly donation',
           description: 'Monthly donation for my website.',
           type: 'INFINITE',
           payment_definitions: [{
             name: 'Monthly donation',
             type: 'REGULAR',
             frequency: 'MONTH',
             frequency_interval: 1,
             cycles: 0,
           }],
           merchant_preferences: {
             auto_bill_amount: 'NO',
             initial_fail_amount_action: 'CONTINUE',
             max_fail_attempts: 0,
           },
         },
         billing_agreement: {
           name: 'Monthly donation for my website',
           description: 'Description of your donation.',
         },
       },
     }
   }

The next step is the same as with one-off payment - let a user verify the transaction on the frontend and then send the received
payload to ``/commerce/payment/capture/$orderID/$paymentID``.

The structure & available options for the payment initialization of Paypal payments follow Paypal PHP SDK library.
Here is the code which transforms the data from the frontend request into Paypal-acceptable format:
`Single Payment <https://github.com/systemseed/commerce_paypal_ec/blob/master/src/PayPal.php#L78>`_ and `Recurring Payment <https://github.com/systemseed/commerce_paypal_ec/blob/master/src/PayPal.php#L97>`_.

Realex (Global Payments)
------------------------

Requires `Commerce Global Payments (Realex) <https://www.drupal.org/project/commerce_globalpayments>`_ module to be enabled & configured (already part of Falcon).

The request should contain data related to order, user and payment. Send the request to ``/commerce/order/create``.
Here's the example of payload part specific to Global Payments (Realex) payment:

.. code-block:: javascript
   :linenos:

   {
     // All order & user specific data.
     ...
     // Payment details.
     payment: {
       gateway: "globalpayments_creditcard_test", // Machine name of payment gateway added by admin.
       type: "globalpayments_credit_card",
       details: {
         name: "John Snow",
         number: "4263970000005262",
         security_code: "123",
         expiration: {
           month: "02",
           year: "2023",
         }
       }
     }
   }

Stripe Payments
---------------

Requires `Commerce Stripe <https://www.drupal.org/project/commerce_stripe>`_ module to be enabled & configured (already part of Falcon).

To make a payment using Stripe, you need to obtain a Stripe token first. It is up to the frontend application to handle it.
For example, React.js has `react-stripe-checkout <https://github.com/azmenak/react-stripe-checkout>`_ library which handles it for you (``token`` method).
Another example for PHP you can find in ``./tests/api/commerce_decoupled_checkout/StripeCest.php``.

As soon as you got the token, the remaining step is straightforward - just send the request to ``/commerce/order/create``.
Here's the example of payload part specific to Stripe Payments:

.. code-block:: javascript
   :linenos:

      {
        // All order & user specific data.
        ...
        // Payment details.
        payment: {
          gateway: "stripe_test", // Machine name of payment gateway added by admin.
          type: "credit_card",
          details: {
            stripe_token: "<INSERT_TOKEN_HERE>",
          }
        }
      }

Direct Debit Payments
---------------------

Direct Debits are enabled by default. The request should contain data related to order, user and payment.
Send the request to ``/commerce/order/create``. Here's the example of payload part specific to Direct Debit payment:

.. code-block:: javascript
   :linenos:

   {
     // All order & user specific data.
     ...
     // Payment details.
     payment: {
       gateway: "direct_debit_test", // Machine name of payment gateway added by admin.
       type: "direct_debit_sepa", // Can be "direct_debit_sepa" or "direct_debit_uk"
       details: {
         account_name: "John Snow",
         swift: "BOFIIE2D",
         iban: "DE89 3704 0044 0532 0130 00",
         debit_date: 2,
         accept_direct_debits: 1,
         one_signatory: 1
     }
   }

Payment Test / Live modes
-------------------------

Every payment gateway has live and test payment modes.

Falcon allows to use test payment modes on any non-production environments.
For the production environment test payments are restricted. To use test
payment mode on production environment you need to set special environment
variables: PAYMENT_SECRET_HEADER_NAME and PAYMENT_SECRET_HEADER_VALUE - and
then set local storage value in the browser using the supplied name and value.

Example:

.. code-block:: php

   PAYMENT_SECRET_HEADER_NAME = X-Payment-Secret
   PAYMENT_SECRET_HEADER_VALUE = 76a67787-af11-4870-b384-b8e85c4fe3b8

And then browser local storage should have
X-Payment-Secret / 76a67787-af11-4870-b384-b8e85c4fe3b8
