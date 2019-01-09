Content
=======

Basic concepts
--------------

#. Falcon follows Drupal best practices to organise content structure: nodes, fields, taxonomy, etc.
#. Each content type has at least title (standard Drupal property), image (``field_image``) and short description (``field_description``) fields.
#. Falcon uses `Paragraphs <https://www.drupal.org/project/paragraphs>`_ to manage visual content (``field_blocks``).
#. Falcon doesn't require to follow these concepts. Instead, it ships with a showcase to demonstrate how these concepts allow building flexible and sophisticated content architectures.

Showcase
--------

Falcon demo content structure is stored in the following features:

- ``falcon_feature_content_structure_demo`` - collection of content types with fields and relationships.
- ``falcon_feature_content_blocks`` - collection of content blocks for the demo content types.
- Default content is **coming soon**.
- Frontend showcase is **coming soon**.

Content blocks
--------------

Content blocks built using `Paragraphs <https://www.drupal.org/project/paragraphs>`_ module.

Paragraphs Browser
~~~~~~~~~~~~~~~~~~

Content blocks are organised in groups using `Paragraphs Browser <http://drupal.org/project/paragraphs_browser>`_ module. Falcon
content showcase includes the following groups:

**Hero**
^^^^^^^^
The blocks at the top of the page, usually a full width image with headings / links.

**Section**
^^^^^^^^^^^
Containers for other blocks (items).

**Items**
^^^^^^^^^
Can be added to sections blocks only (i.e. child blocks).

**Widgets**
^^^^^^^^^^^
Rich interactive elements.

**Listing**
^^^^^^^^^^^

Automated / semi-automated listings, i.e. "Latest content" block.

Paragraph machine name should begin with its group name, i.e. "Latest content" block
should be named ``listing_latest_content``.

Reusable content blocks
~~~~~~~~~~~~~~~~~~~~~~~

Reusable content blocks use Paragraphs Library module from Paragraphs package in combination with Entity Browser for better editorial experience.


Media
--------------

Falcon Media tools are based on Drupal core and fully compatible with Media Browser. It is recommended to use Media fields instead of legacy Image/File fields when developing sites on Falcon.

Basic media configuration should be stored in Falcon Media feature. It provides setup for images (all popular formats and SVG) and videos (local and external).

**Note:** Falcon  uses `Video Embed Field <https://www.drupal.org/project/video_embed_field>`_ solution for external videos until `[#2996029] Add oEmbed support <https://www.drupal.org/project/drupal/issues/2996029>`_ to the media library is solved.
