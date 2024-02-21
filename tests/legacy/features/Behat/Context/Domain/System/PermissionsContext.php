<?php

namespace Pim\Behat\Context\Domain\System;

use Context\Spin\SpinCapableTrait;
use PHPUnit\Framework\Assert;
use Pim\Behat\Context\PimContext;

/**
 * This context regroups all methods related to a permission section
 */
class PermissionsContext extends PimContext
{
    use SpinCapableTrait;

    /**
     * @param string $action
     * @param string $api
     * @param string $type
     * @param string $acls
     *
     * @throws \InvalidArgumentException If $action is not a defined method
     *
     * @When /^I (grant|revoke) rights to( API)? (resources?|groups?) (.*)$/
     */
    public function iSetRightsToACL($action, $api, $type, $acls)
    {
        if (false !== strpos($type, 'resource')) {
            $type = 'Resource';
        } elseif (false !== strpos($type, 'group')) {
            $type = 'Group';
        }

        $element = $api ? 'API permission' : 'Permission';
        $permissionElement = $this->getElementOnCurrentPage($element);

        switch ($action) {
            case 'grant':
                $method = 'grant' . $type;
                break;
            case 'revoke':
                $method = 'revoke' . $type;
                break;
            default:
                throw new \InvalidArgumentException(sprintf('Action "%s" does not exist.', $action));
                break;
        }

        foreach ($this->listToArray($acls) as $acl) {
            $permissionElement->$method($acl);
        }
    }

    /**
     * @When /^I grant all rights$/
     */
    public function iGrantAllRightsToACLResources()
    {
        $iconSelector = '.acl-permission .acl-permission-toggle.non-granted';

        $this->getSession()->executeScript(
            sprintf('$("%s").each(function () { $(this).click(); });', $iconSelector)
        );
    }

    /**
     * @param string $api
     * @param string $acls
     * @param string $action
     *
     * @throws \InvalidArgumentException If $action is not a defined method
     *
     * @When /^I should see( API)? resources? (.*) (granted|revoked)$/
     */
    public function iShouldSeeRightsOnACL($api, $acls, $action)
    {
        $element = $api ? 'API permission' : 'Permission';

        $permissionElement = $this->getElementOnCurrentPage($element);

        switch ($action) {
            case 'granted':
                $method = 'isGrantedResource';
                break;
            case 'revoked':
                $method = 'isRevokedResource';
                break;
            default:
                throw new \InvalidArgumentException(sprintf('Action "%s" does not exist.', $action));
                break;
        }

        foreach ($this->listToArray($acls) as $acl) {
            Assert::assertTrue($permissionElement->$method($acl));
        }
    }
}
