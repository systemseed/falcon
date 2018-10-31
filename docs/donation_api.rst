Donation API
============

Falcon uses `Commerce Decoupled Checkout<https://www.drupal.org/project/commerce_decoupled_checkout>`_ as a donation API endpoint at `/commerce/order/create`.

Here's link to payload documenttion that this module expects. Below you will find more examples how this is used on Falcon.

Alternatively, you can look at `./tests/api/commerce_decoupled_checkout/DonationCest.php` for more examples.

Payment Gateways
----------------

Falcon provides several payment gateways encapsulated into features:

    - Example payment gateways for testing purposes (`example_test` and `example_live`); supports only `credit_card` payment method type.
    - Direct debit payment gateways (`direct_debit_test` and `direct_debit_live`); supports `direct_debit_sepa` and `direct_debit_uk` payment method types.

Examples:

.. code-block:: php
   :linenos:

   {
     "order": {
       "type": "donation"
       "field_appeal": 1 // Appeal ID,
       "order_items": [
         {
           "type": "donation",
           "field_donation_type": "single_donation",
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
       "gateway": "example_test",
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

.. code-block:: php
   :linenos:

      {
        "order": {
          "type": "donation"
          "field_appeal": 1 // Appeal ID,
          "order_items": [
            {
              "type": "donation",
              "field_donation_type": "recurring_donation",
              "purchased_entity": {
                "sku": "donation"
              },
              "unit_price": {
                "number": 10,
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
          "gateway": "direct_debit_test",
          "type": "direct_debit_sepa",
          "details": {
            "account_name": "John Snow",
            "swift": "BOFIIE2D",
            "iban": "DE89 3704 0044 0532 0130 00",
            "debit_date": 2,
            "accept_direct_debits": 1,
            "one_signatory": 1
          },
        },
      }

Payment Modes
-------------

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
