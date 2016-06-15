<?php

namespace Pim\Behat\Context\Domain\System;

use Pim\Behat\Context\PimContext;

/**
 * This context regroups all methods related to a permission section
 */
class PermissionsContext extends PimContext
{
    /**
     * @param string $action
     * @param string $acls
     *
     * @throws \InvalidArgumentException If $action is not a defined method
     *
     * @When /^I (grant|revoke) rights to (resources?|groups?) (.*)$/
     */
    public function iSetRightsToACL($action, $type, $acls)
    {
        if (false !== strpos($type, 'resource')) {
            $type = 'Resource';
        } elseif (false !== strpos($type, 'group')) {
            $type = 'Group';
        }

        $permissionElement = $this->getCurrentPage()->getElement('Permission');
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
}
