Environment
===========

Falcon is using the environment variable called ENVIRONMENT to recognize if it is running in production mode.

If the value of ENVIRONMENT is `production` then Falcon is running in production mode.
If it has any other value - then it is running in non-production mode.

There is a Drupal service to use in any custom code to determine the environment - `falcon_common.environment`.
You can inject it and then use like this: `$this->environment->isProduction()`.
