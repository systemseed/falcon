Environment
===========

Falcon is using the environment variable called ENVIRONMENT to recognize if it is running in production mode.

Supported values are:

- `production` - if the value of ENVIRONMENT is `production` then Falcon is running in production mode. If it has any other value - then it is running in non-production mode.
- `development` - if the value of ENVIRONMENT is `development` then Falcon is running in development mode.

In order to use this environment variable on the PHP code level you need to set it. Local environment powered by Docker
and Makefile sets it for the `php` service in the .docker/docker-compose.override.yml file taking the value from the .env file.
To set this environment variable on the hosting environment you should use the hosting provider tools.

There is a Drupal service to use in any custom code to determine the environment - `falcon_common.environment`.
You can inject it and then use like this: `$this->environment->isProduction()`.

.. code-block:: php
   :linenos:

   services:
     module_name.service_name:
       class: Drupal\module_name\ServiceName
       arguments: ['@falcon_common.environment']

.. code-block:: php
   :linenos:

   <?php

   namespace Drupal\module_name;

   /**
    * Class ServiceName.
    */
   class ServiceName implements ServiceNameInterface {

     /**
      * The environment.
      *
      * @var \Drupal\falcon_common\EnvironmentInterface
      */
     protected $environment;

     /**
      * Constructs a new Payment object.
      *
      * @param \Drupal\falcon_common\EnvironmentInterface $environment
      *   The environment.
      */
     public function __construct(RequestStack $request_stack, EnvironmentInterface $environment) {
       $this->request = $request_stack->getCurrentRequest();
       $this->environment = $environment;
     }

     public function methodName() {
       $if (this->environment->isProduction()) {
         // Do smth related to production environment.
       }
     }

   }
