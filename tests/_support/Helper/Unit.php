<?php
namespace Helper;

class Unit extends \Codeception\Module
{

    /**
     * Returns names of Drupal development modules.
     *
     * @return array
     */
    public function getDevelopmentModules()
    {
        $modules = explode(' ', str_replace("'",'', getenv('DEVELOPMENT_MODULES')));
        return (empty($modules) ? [] : $modules);
    }
}
