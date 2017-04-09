<?php

namespace PimEnterprise\Behat\Context;

use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Pim\Behat\Context\DBALPurger;
use Pim\Behat\Context\HookContext as BaseHookContext;

class HookContext extends BaseHookContext
{
    /**
     * @BeforeScenario
     *
     * @param BeforeScenarioScope $scope
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $environment = $scope->getEnvironment();
        $this->mainContext = $environment->getContext($this->mainContextClass);
    }

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
        $sqlTables = [
            'pimee_teamwork_assistant_completeness_per_attribute_group',
            'pimee_teamwork_assistant_project_product',
        ];

        $purger = new DBALPurger($this->getService('database_connection'), $sqlTables);

        $purger->purge();

        parent::purgeDatabase();
    }
}
