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

Example:

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
       "gateway": "example",
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

Payment Gateways
----------------

Falcon provides several payment gateways encapsulated into features:

    - Example payment gateway for testing purposes; supports only `credit_card` payment method type.
