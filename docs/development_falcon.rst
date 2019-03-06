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


Version releases
----------------

Create new release
~~~~~~~~~~~~~~~~~~

You can read official Github `instruction <https://help.github.com/en/articles/creating-releases>`_ for more info how to create releases.

Requirements
~~~~~~~~~~~~
- New release should be based on master branch.
- New release tag should look like "1.0.0".

Given a version number MAJOR.MINOR.PATCH, increment the:

- MAJOR version when you make incompatible API changes.
- MINOR version when you add functionality in a backwards-compatible manner.
- PATCH version when you make backwards-compatible bug fixes.

You can read `documentation <https://semver.org/>`_ for more info about version standardization.
