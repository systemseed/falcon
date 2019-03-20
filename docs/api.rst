Content API
===========

Falcon supports multiple ways to expose content via API:

- `JSON:API <https://www.drupal.org/project/jsonapi>`_
- `RESTful Web Services <https://www.drupal.org/docs/8/core/modules/rest>`_
- `Views Rest <https://www.drupal.org/docs/8/core/modules/rest/get-on-views-generated-lists>`_
- `Rest Entity Recursive <https://www.drupal.org/project/rest_entity_recursive>`_

You are free to use any of the options above to meet your project goals.

JSON:API
--------
JSON:API is recommended option for API exposure in Falcon.
With JSON:API & JSON:API Extras (both installed in Falcon), you most likely will be able to cover most your API-related tasks.

However, please be aware of well-known JSON:API limitations:

- In case of more complex content structures (nested Media, Paragraphs, etc) JSON:API queries may become hard to maintain.
- There is no way to make cross-bundle requests. `Follow this issue to stay up to date <https://www.drupal.org/project/jsonapi_extras/issues/2956414>`_.
- JSON:API doesn't support aliases and redirects out of the box. You will need help of `Decoupled Router <https://www.drupal.org/project/decoupled_router>`_ of similar modules.

**Usage**::

    /jsonapi/node/my-bundle

Read more about JSON:API in the official `documentation <https://www.drupal.org/docs/8/modules/jsonapi/jsonapi>`_.

RESTful Web Services
--------------------
With RESTful Web Services module you can enable built-in endpoints for various content types
Default REST endpoints have a couple of drawbacks:

- No way to include related entities.
- No way to expose listings.

**Usage:**

Go to ``/admin/config/services/rest`` page and enable "Content" resource. Make a request::

    /node/1?_format=json
    or
    /node-alias?_format=xml


**Custom resource:**

With REST module you can create your own REST endpoints.

Read more about RESTful Web Services in the official `documentation <https://www.drupal.org/docs/8/api/restful-web-services-api/custom-rest-resources>`_.

REST Views
----------
You can create a view with REST export display to expose dynamic lists of content via API.
Known drawbacks of this approach:

- You have to reconfigure your view if business logic of the app has been changed.
- You have to create similar views for different listings on your site.

Falcon will be return the response with pagination data, but if you want to use ``page`` or ``item_per_page`` in your request you should configure **exposed options** in you **pager options**.

In Falcon, we have enhanced pagination support for REST Views (via patch).

Example request::

    /view-url?_format=json&page=3


Read more about JSON:API in the official `documentation <https://www.drupal.org/docs/8/core/modules/rest/get-on-views-generated-lists>`_.

Rest Entity Recursive
---------------------
Rest Entity Recursive provides new "json_recursive" REST format which exposes all fields and referenced entities by default. You may want to use this module if you need to fetch almost everything in one request.

**Usage:**
Enable core REST resource or create REST View display with format ``json_recursive`` enabled. Make a request::

    /request-url?_format=json_recursive

You can use query parameter ``max_depth`` in you want to limit depth of loaded references: . Default is ``max_depth = 10``.

Find examples how to customize and fine-tune the output in submodules:

- rest_media_recursive
- rest_paragraphs_recursive

Read more about Rest Entity Recursive on `Drupal.org project page <https://www.drupal.org/project/rest_entity_recursive>`_.
