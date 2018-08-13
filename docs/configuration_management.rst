Configuration management
========================

General approach
----------------

All configuration is stored in code. Any configuration change done directly on
production environment will be reset on next deploy.

To allow clients to change configuration we should use the `Config Pages <https://www.drupal.org/project/config_pages>`_
module approach.
