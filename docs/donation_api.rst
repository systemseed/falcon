Donation API
============

Falcon provides donation API endpoint at /falcon/donation.

It accepts POST requests in JSON format with the following fields:

    - `donation_type`: either `single_donation` or `recurring_donation`
    - `order`: object with following fields:

        - `field_appeal`: reference to the Appeal by node ID

    - `payment`: payment description object:

        - `amount`
        - `currency_code`: "EUR" or any other
        - `gateway`: payment gateway identifier
        - `method`: payment method object:

            - `type`: payment method type, "credit_card"
            - `options`: payment method options object, fields are dependent on chosen payment method

    - `profile`: object with user supplied information:

        - `email`
        - `field_first_name`
        - `field_last_name`
        - `field_phone`
        - `field_contact_email`
        - `field_contact_phone`
        - `field_contact_post`
        - `field_contact_sms`
        - `address`: address information object following the OASIS eXtensible Address Language (xAL) standard.
          See the example below and `Address module <https://www.drupal.org/project/address>`_ for details.

Payment Gateways
----------------

Falcon provides several payment gateways encapsulated into features:

    - Example payment gateways for testing purposes (`example_test` and `example_live`); supports only `credit_card` payment method type.
    - Direct debit payment gateways (`direct_debit_test` and `direct_debit_live`); supports `direct_debit_sepa` and `direct_debit_uk` payment method types.

Examples:

.. code-block:: php
   :linenos:

   {
     "donation_type": "single_donation",
     "order": {
       "field_appeal": 1
     },
     "payment": {
       "amount": 10,
       "currency_code": "EUR",
       "gateway": "example_test",
       "method": {
         "type": "credit_card",
         "options": {
           "type": "visa",
           "number": "4111111111111111",
           "expiration": {
             "month": "01",
             "year": "2022"
           }
         }
       }
     ,
     "profile": {
       "email" : "test@systemseed.com",
       "field_first_name": "John",
       "field_last_name": "Snow",
       "field_phone": "88001234567",
       "field_contact_email": 1,
       "field_contact_phone": 0,
       "field_contact_post": 0,
       "field_contact_sms": 0,
       "address": {
         "country_code": "US",
         "address_line1": "1098 Alta Ave",
         "locality": "Mountain View",
         "administrative_area": "CA",
         "postal_code": "94043"
       }
     }
   }

.. code-block:: php
   :linenos:

   {
     "donation_type": "recurring_donation",
     "order": {
       "field_appeal": 1
     },
     "payment": {
       "amount": 10,
       "currency_code": "EUR",
       "gateway": "direct_debit_test",
       "method": {
         "type": "direct_debit_sepa",
         "options": {
           "account_name": "John Snow",
           "swift": "BOFIIE2D",
           "iban": "DE89 3704 0044 0532 0130 00",
           "debit_date": 2,
           "accept_direct_debits": 1,
           "one_signatory": 1
         }
       }
     ,
     "profile": {
       "email" : "test@systemseed.com",
       "field_first_name": "John",
       "field_last_name": "Snow",
       "field_phone": "88001234567",
       "field_contact_email": 1,
       "field_contact_phone": 0,
       "field_contact_post": 0,
       "field_contact_sms": 0,
       "address": {
         "country_code": "US",
         "address_line1": "1098 Alta Ave",
         "locality": "Mountain View",
         "administrative_area": "CA",
         "postal_code": "94043"
       }
     }
   }

.. code-block:: php
   :linenos:

   {
     "donation_type": "recurring_donation",
     "order": {
       "field_appeal": 1
     },
     "payment": {
       "amount": 10,
       "currency_code": "EUR",
       "gateway": "direct_debit_test",
       "method": {
         "type": "direct_debit_uk",
         "options": {
           "account_name": "John Snow",
           "sort_code": "123456",
           "account_number": "12345678",
           "debit_date": 2,
           "accept_direct_debits": 1,
           "one_signatory": 1
         }
       }
     ,
     "profile": {
       "email" : "test@systemseed.com",
       "field_first_name": "John",
       "field_last_name": "Snow",
       "field_phone": "88001234567",
       "field_contact_email": 1,
       "field_contact_phone": 0,
       "field_contact_post": 0,
       "field_contact_sms": 0,
       "address": {
         "country_code": "US",
         "address_line1": "1098 Alta Ave",
         "locality": "Mountain View",
         "administrative_area": "CA",
         "postal_code": "94043"
       }
     }
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
