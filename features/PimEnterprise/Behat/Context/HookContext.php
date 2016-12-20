<?php

namespace PimEnterprise\Behat\Context;

use Pim\Behat\Context\DBALPurger;
use Pim\Behat\Context\HookContext as BaseHookContext;

class HookContext extends BaseHookContext
{
    /**
     * @BeforeScenario
     */
    public function registerConfigurationDirectory()
    {
        $this->getMainContext()->getSubcontext('catalogConfiguration')
            ->addConfigurationDirectory(__DIR__.'/../../../Context/catalog');
    }

    /**
     * @BeforeScenario
     */
    public function purgeDatabase()
    {
        parent::purgeDatabase();

        $purger = new DBALPurger(
            $this->getService('database_connection'),
            [
                'akeneo_activity_manager_completeness_per_attribute_group',
                'akeneo_activity_manager_project_product',
            ]
        );

        $purger->purge();
    }
}
