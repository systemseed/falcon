Testing
=======

Overview
-------------

Falcon uses Codeception framework to test its functions, modules, features and
API endpoints. Tests are integrated in development workflow using Circle CI.

Every time when someone opens a new pull request on Github, Circle CI executes
the following tasks:

- Checkout code from the corresponding git branch.
- Spin up docker containers.
- Install Falcon and all its features.
- Run tests.

If you are interested in details of Falcon CI setup, you can find latest Circle CI configuration file here: https://github.com/systemseed/falcon/blob/master/.circleci/config.yml

Running tests on local
----------------------

First, run ``make tests:prepare`` to initialize testing framework.
Now, you can run all Codeception tests using ``make tests:run`` command. You can
run a specific test suite by passing its name as a second parameter: ::

  # Run API test suite only
  make tests:run api
  # Run unit test suite only
  make tests:run unit

You can pass any extra options to ``codecept`` bin if you need: ::

  # Run API tests from group "failed".
  make tests:run api -- -g failed

Tests structure
---------------

There are two Codeception test suites in ``/tests`` folder: API and Unit.
Both have connection to Drupal 8 API and to the database which allows developers to
implement sophisticated and granular tests.

API tests
~~~~~~~~~

This type of tests is suitable for testing of API (REST) endpoints.
You can find examples of API tests here: https://github.com/systemseed/falcon/tree/master/tests/api

Read more: https://codeception.com/docs/modules/REST#Actions

Unit tests
~~~~~~~~~~
Suitable for classic unit tests and **integration tests** with runtime access to Drupal code and database.

You can find examples of unit tests here: https://github.com/systemseed/falcon/tree/master/tests/unit

Read more: https://codeception.com/docs/05-UnitTests

Writing tests
-------------

Each new feature, or API endpoint, or pure PHP function should be covered by tests.

When adding a new test to the Falcon distribution developer should decide if
it will cover basic distribution installation  or not.
If it covers basic installation distribution it should be added to ``basic`` tests group. If not than it should be added to ``additional`` group.
**For example:** ::

    /**
     * @group basic
     */
    public function testBasic()

If you wrote a test for ``additional`` group than you should add module this test covers into ``ADDITIONAL_MODULES`` variable in ``env.default`` file.
It's recommended to store tests in subfolders with the same name
as Falcon module or feature to test. For example, if you want to
test function ``falcon_development_install`` you need to put your test into
``tests/unit/falcon_development/`` folder.

Before you start writing tests we recommend to run the following command: ::

  # Optional. Creates ".codecept" folder with Codeception sources in project root.
  make tests:autocomplete

It will enable autocomplete of available test methods in most of popular IDEs.
Now you can start writing tests and run them using ``make tests:run``.

To access ``codecept`` cli directly run: ::

  make tests:cli

It will allow you to run codecept commands to generate new tests or run a specific
test if you need. See list of available commands here: https://codeception.com/docs/reference/Commands
