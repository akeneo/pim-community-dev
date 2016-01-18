<?php

namespace Context\Page\Role;

use Context\Page\Base\Form;

/**
 * User role edit page
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Edit extends Form
{
    /**
     * @var string
     */
    protected $path = '/user/role/update/{id}';

    /**
     * Grant rights to all ACL resources
     */
    public function grantAllResourceRights()
    {
        $iconSelector = '.acl-permission i.acl-permission-toggle.non-granted';

        $this->getSession()->executeScript(
            sprintf('$("%s").each(function () { $(this).click(); });', $iconSelector)
        );
    }

    /**
     * Grant ACL resource rights
     *
     * @param string $resource
     */
    public function grantResourceRights($resource)
    {
        $resourceSelector = sprintf(".acl-permission strong:contains('%s')", $resource);
        $iconSelector     = 'i.acl-permission-toggle.non-granted';

        $this->getSession()->executeScript(
            sprintf('$("%s").parent().parent().find("%s").click();', $resourceSelector, $iconSelector)
        );
    }

    /**
     * Remove ACL resource rights
     *
     * @param string $resource
     */
    public function removeResourceRights($resource)
    {
        $resourceSelector = sprintf(".acl-permission strong:contains('%s')", $resource);
        $iconSelector     = 'i.acl-permission-toggle.granted';

        $this->getSession()->executeScript(
            sprintf('$("%s").parent().parent().find("%s").click();', $resourceSelector, $iconSelector)
        );
    }
}
