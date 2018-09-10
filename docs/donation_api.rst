Donation API
============

Falcon provides donation API endpoint at /falcon/donation.

It accepts POST requests in JSON format with the following fields:
    - `order`: object with following fields:

        - `field_appeal`: reference to the Appeal by node ID

    - `payment`: object with `amount` and `currency_code`
    - `donation_type`: either `single_donation` or `recurring_donation`
    - `profile`: object with user supplied information:

        - `email`
        - `field_first_name`
        - `field_last_name`
        - `field_phone`
        - `field_contact_email`
        - `field_contact_phone`
        - `field_contact_post`
        - `field_contact_sms`

Example:

.. code-block:: php
   :linenos:

   {
     "order": {
       "field_appeal": 1,
     },
     "payment": {
       "amount": 15,
       "currency_code": "EUR"
     },
     "donation_type": "single_donation",
     "profile": {
       "email" : "test@test.com",
       "field_first_name": "First",
        "field_last_name": "Last",
       "field_phone": "88001234567",
       "field_contact_email": 1,
       "field_contact_phone": 0,
       "field_contact_post": 0,
       "field_contact_sms": 0
     }
   }
