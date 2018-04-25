<?php

namespace Context;

use PHPUnit\Framework\Assert;

class EnterpriseSecurityContext extends SecurityContext
{
    /**
     * @When /^I make a direct authenticated DELETE call on the "([^"]*)" rule$/
     */
    public function iMakeADirectAuthenticatedDeleteCallOnTheRule($ruleCode)
    {
        $routeName = 'pimee_catalog_rule_rule_delete';

        $rule = $this->getService('akeneo_rule_engine.repository.rule_definition')
            ->findOneByIdentifier($ruleCode);

        $url = $this->getService('router')
            ->generate($routeName, ['id' => $rule->getId()]);

        $this->doCall('DELETE', $url);
    }

    /**
     * @When /^I make a direct authenticated DELETE call on the "([^"]*)" asset$/
     */
    public function iMakeADirectAuthenticatedDeleteCallOnTheAsset($assetCode)
    {
        $routeName = 'pimee_product_asset_remove';

        $asset = $this->getService('pimee_product_asset.repository.asset')
            ->findOneByIdentifier($assetCode);

        $url = $this->getService('router')
            ->generate($routeName, ['id' => $asset->getId()]);

        $this->doCall('DELETE', $url);
    }

    /**
     * @When /^I make a direct authenticated POST call to accept the last proposal of user "([^"]*)"$/
     */
    public function iMakeADirectAuthenticatedPostCallToAcceptTheLastProposalOfUser($username)
    {
        $routeName = 'pimee_workflow_product_draft_rest_approve';

        $proposal = $this->getService('pimee_workflow.repository.product_draft')
            ->findOneBy(['author' => $username]);

        $url = $this->getService('router')
            ->generate($routeName, ['id' => $proposal->getId()]);

        $this->doCall('POST', $url);
    }

    /**
     * @When /^I make a direct authenticated POST call to reject the last proposal of user "([^"]*)"$/
     */
    public function iMakeADirectAuthenticatedPostCallToRejectTheLastProposalOfUser($username)
    {
        $routeName = 'pimee_workflow_product_draft_rest_reject';

        $proposal = $this->getService('pimee_workflow.repository.product_draft')
            ->findOneBy(['author' => $username]);

        $url = $this->getService('router')
            ->generate($routeName, ['id' => $proposal->getId()]);

        $this->doCall('POST', $url);
    }

    /**
     * @When /^I make a direct authenticated POST call to remove the last proposal of user "([^"]*)"$/
     */
    public function iMakeADirectAuthenticatedPostCallToRemoveTheLastProposalOfUser($username)
    {
        $routeName = 'pimee_workflow_product_draft_rest_remove';

        $proposal = $this->getService('pimee_workflow.repository.product_draft')
            ->findOneBy(['author' => $username]);

        $url = $this->getService('router')
            ->generate($routeName, ['id' => $proposal->getId()]);

        $this->doCall('POST', $url);
    }


    /**
     * @Then /^there should be a "([^"]*)" asset$/
     */
    public function thereShouldBeAAsset($assetCode)
    {
        $asset = $this->getService('pimee_product_asset.repository.asset')
            ->findOneByIdentifier($assetCode);

        Assert::assertNotNull($asset);
    }

    /**
     * @Then /^there should be a "([^"]*)" rule$/
     */
    public function thereShouldBeARule($ruleCode)
    {
        $rule = $this->getService('akeneo_rule_engine.repository.rule_definition')
            ->findOneByIdentifier($ruleCode);

        Assert::assertNotNull($rule);
    }
}
