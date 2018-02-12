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
     * @When /^I make a direct authenticated DELETE call on the "([^"]*)" asset$/
     */
    public function iMakeADirectAuthenticatedDeleteCallOnTheAsset($assetCode)
    {
        $routeName = 'pimee_product_asset_remove';

        $asset = $this->kernel
            ->getContainer()
            ->get('pimee_product_asset.repository.asset')
            ->findOneByIdentifier($assetCode);

        $url = $this->kernel
            ->getContainer()
            ->get('router')
            ->generate($routeName, ['id' => $asset->getId()]);

        $this->doCall('DELETE', $url);
    }

    /**
     * @Then /^there should be a "([^"]*)" asset$/
     */
    public function thereShouldBeAAsset($assetCode)
    {
        $asset = $this->kernel
            ->getContainer()
            ->get('pimee_product_asset.repository.asset')
            ->findOneByIdentifier($assetCode);

        assertNotNull($asset);
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
