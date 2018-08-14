<?php

namespace falcon_development;

use Drupal\user\Entity\Role;

class DevelopmentTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * Checks that specified dev modules are enabled.
     */
    public function testDevelopmentModulesEnabled()
    {
        foreach ($this->tester->getDevelopmentModules() as $module) {
            $this->assertTrue(
                \Drupal::moduleHandler()->moduleExists($module),
                "Module $module should be enabled."
            );
        }
    }

    /**
     * Checks that test users exist and enabled.
     */
    public function testTestUsersEnabled()
    {
        /* @var $role \Drupal\user\Entity\Role */
        foreach (Role::loadMultiple() as $role) {
            if ($role->id() == 'anonymous') {
                continue;
            }

            $this->tester->seeInDatabase(
                'users_field_data',
                ['name' => $role->id() . '.test', 'status' => 1]
            );
        }
    }
}
