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
