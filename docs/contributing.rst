Contributing
============

Coding Standards
----------------

Falcon follows `Drupal 8 Coding standards <https://www.drupal.org/docs/develop/standards>`_.

To check code on your local environment, run the following commands:

#. ``make phpcs`` - checks all PHP code against `Drupal` and `DrupalPractice` standards.
#. ``make phpcbf`` - attempts to automatically fix some of found issues in PHP code.
#. ``make eslint`` - checks JavaScript code using `Drupal core eslint config <https://www.drupal.org/docs/develop/standards/javascript/eslint-settings>`_.
#. ``make eslint -- --fix`` - attempts to automatically fix some of found issues in JS code.

Documentation
--------------
We'd love to have you helping with Falcon documentation.

All documentation is stored in the main Falcon repo in ``docs`` folder. We use
reStructuredText format and readthedocs.org hosting for our documentation.

Getting started
~~~~~~~~~~~~~~~
You can start contributing using `GitHub edit feature <https://help.github.com/articles/editing-files-in-your-repository/>`_.
All you need to do is to clone the repo, edit one of `.rst` files and submit PR as usual.

Haven't used `reStructuredText <http://www.sphinx-doc.org/en/master/usage/restructuredtext/basics.html>`_ before? Don't worry! Check out a couple of examples
to get started.


Building on local
~~~~~~~~~~~~~~~~~
If you'd like to build Falcon docs on your local please uncomment ``sphinxdocs``
service in ``docker-compose.override.yml`` and run ``make up``.
Now all your changes in ``docs`` folder are tracked and automatically built in
``docs/_build/html`` folder.

