Falcon Development
==================

Naming conventions
------------------
Custom modules should be placed into the `modules/custom` directory and
prefixed with `falcon_` string, for instance `falcon_example`.

Packaged features should be placed into `modules/features` directory and
prefixed with `falcon_feature_` string, for instance `falcon_feature_example`.

Adding a new module
-------------------
When adding a new module to the Falcon distribution developer should decide if
it is needed on every site consuming the distribution. If this is the case then
it should be enabled using hook_update_N() in the `falcon_deploy` module and
also added as a dependency into falcon.info.yml file so it gets installed on
new installations.
If the module is not required on every site then it should either be a
dependency for some other modules/features or enabled by the team managing
specific client site.

Falcon Dashboard
----------------

Falcon provides a lightweight administration dashboard for users
who don't need full access to all Drupal administration tools.

The dashboard is available at ``/dashboard`` and integrated with
`Admin Toolbar <https://www.drupal.org/project/admin_toolbar>`_.

Permissions
~~~~~~~~~~~

- Users need *"Use the administration toolbar"* permission to access Dashboard
  from the toolbar.
- Users need *"Use default admin toolbar"* permission to access Administration
  menu from the toolbar.

There are two ways to place new categories and items on the dashboard:

#. Export config menu items into features using
   `Config menu link <https://www.drupal.org/project/menu_link_config>`_ module.
   Preferred option for Falcon features.
#. Add normal menu items (content level) and flush cache.
