Client-site Development
=======================

General approach
----------------
Falcon is using `Features module<https://drupal.org/project/features>`_ to
package configurations.
Please see `Features handbook<https://www.drupal.org/docs/8/modules/features>`_
for detailed information on Features.

When feature is installed for the first time its configuration files are
getting imported into site configuration - once it is done then this
configuration is owned by the site.

It is recommended to use the `Config Distro<https://www.drupal.org/project/config_distro>`_
module to manage configuration updates coming from Falcon distribution.

How to install Falcon feature?
------------------------------
Go to `/admin/modules` and install it.

How to update Falcon feature?
-----------------------------
After updating the codebase to the new version of Falcon go to
`/admin/config/development/configuration/distro` and import feature updates.

TODO: check if it can be done automatically in hook_update_N() or using drush
command `config-distro-update`.

How to remove Falcon feature?
-----------------------------
Go to `/admin/modules/uninstall` and uninstall it.
Then go to `/admin/config/development/features`, review the configurations
supplied with the uninstalled feature and remove the configurations you don't
need anymore.

TODO: Is there (or can we provide) a way to automate this through hook_update
or some drush command? There should be some automated upgrade path from old
configs to new configs list. Maybe through hook_update or something like that.
