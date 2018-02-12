<?php

namespace Context;

class EnterpriseSecurityContext extends SecurityContext
{
    /**
     * @When /^I make a direct authenticated DELETE call on the "([^"]*)" rule$/
     */
    public function iMakeADirectAuthenticatedDeleteCallOnTheRule($ruleCode)
    {
        $routeName = 'pimee_catalog_rule_rule_delete';

        $rule = $this->kernel
            ->getContainer()
            ->get('akeneo_rule_engine.repository.rule_definition')
            ->findOneByIdentifier($ruleCode);

        $url = $this->kernel
            ->getContainer()
            ->get('router')
            ->generate($routeName, ['id' => $rule->getId()]);

        $this->doCall('DELETE', $url);
    }

    /**
     * @Then /^there should be a "([^"]*)" rule$/
     */
    public function thereShouldBeARule($ruleCode)
    {
        $rule = $this->kernel
            ->getContainer()
            ->get('akeneo_rule_engine.repository.rule_definition')
            ->findOneByIdentifier($ruleCode);

        assertNotNull($rule);
    }
}
