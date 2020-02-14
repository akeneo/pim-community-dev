<?php

namespace PimEnterprise\Behat\Context;

use Behat\Behat\Hook\Scope\BeforeScenarioScope;
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
}
