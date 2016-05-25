<?php

namespace Pim\Behat\Context\Domain\System;

use Pim\Behat\Context\PimContext;

/**
 * This context regroups all methods related to a permission section
 */
class PermissionsContext extends PimContext
{
    /**
     * @param string $permission
     * @param string $resources
     *
     * @When /^I (grant|revoke) rights to resources? (.*)$/
     */
    public function iSetRightsToACLResources($permission, $resources)
    {
        foreach ($this->listToArray($resources) as $resource) {
            $this->getCurrentPage()->executeActionOnResource($permission, $resource);
        }
    }

    /**
     * @When /^I grant all rights$/
     */
    public function iGrantAllRightsToACLResources()
    {
        $this->getCurrentPage()->grantAllResourceRights();
    }

    /**
     * @param string $action (grant|remove)
     * @param string $group
     *
     * @When /^I (grant|revoke) rights to groups? (.*)$/
     */
    public function iSetRightsToACLGroups($action, $groups)
    {
        foreach ($this->listToArray($groups) as $group) {
            $this->getCurrentPage()->executeActionOnGroup($action, $group);
        }
    }
}
