API
=======

Overview
--------

Falcon supported difference ways to work with api.

- `JsonApi <https://www.drupal.org/project/jsonapi>`_
- `RESTful Web Services <https://www.drupal.org/docs/8/core/modules/rest>`_
- `Views Rest <https://www.drupal.org/docs/8/core/modules/rest/get-on-views-generated-lists>`_
- `Rest Entity Recursive <https://www.drupal.org/project/rest_entity_recursive>`_

You can choose any of ways for your tasks.

JsonApi
~~~~~~~

JsonApi very powerfull module. With this module we can cover most our requests.
But today jsonApi has a couple of drawbacks:

- Need include related entities therefore we can have a hard request.
- No way to do a cross-bundles filter. We believe it will be come soon.

**Usage**::

    /jsonapi/node/my-bundle

You can read official `JsonApi documentation <https://www.drupal.org/docs/8/modules/jsonapi/jsonapi>`_ for more info how to create different requests.

Rest
~~~~

With Rest module you can enable defaults endpoints for different content types for expose single entity.
Default Rest endpoints has a couple of drawbacks:

- No way to include related entities.
- No way to expose listings.

**Usage:**

Go to ``/admin/config/services/rest`` page and enable "Content" resource. Do request::

    /node/1?_format=json
    or
    /node-alias?_format=xml


**Custom resource:**

Also with Rest module you can write your own rest endpoint with your custom response.
You can read official `instruction <https://www.drupal.org/docs/8/api/restful-web-services-api/custom-rest-resources>`_ for more info how to create custom rest resource.

Views Rest
~~~~~~~~~~

You can create view with rest export and configure it.
View with rest export has a couple of drawbacks:

- You should reconfigure your view if your logic was changed.
- You should create the similar views for different content types.

Falcon will be return the response with pagination data, but if you want to use ``page`` or ``item_per_page`` in your request you should configure **exposed options** in you **pager options**.

You can read official `instruction <https://www.drupal.org/docs/8/core/modules/rest/get-on-views-generated-lists>`_ for more info how to create view with rest export.

Rest Entity Recursive
~~~~~~~~~~~~~~~~~~~~~

With this module you can get entities with include related entities.
It provides new "json_recursive" REST format which exposes all fields and referenced entities by default. You may want to use this module if you need to fetch almost everything in one request.

**Usage:**
Enable Rest resource or create view with format ``json_recursive``. Do request::

    /request-url?_format=json_recursive

You can use key ``max_depth`` in you request for limit depth of loaded references: . Default ``max_depth = 10``.
Also you can customize the output in submodules:

- rest_media_recursive
- rest_paragraphs_recursive

You can read `documentation <https://www.drupal.org/project/rest_entity_recursive>`_ for more info about this module.
