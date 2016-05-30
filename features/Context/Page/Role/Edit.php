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
     * {@inheritdoc}
     */
    public function __construct($session, $pageFactory, $parameters = [])
    {
        parent::__construct($session, $pageFactory, $parameters);

        $this->elements = array_merge(
            [
                'Role' => [
                    'css'        => '#rights-action',
                    'decorators' => [
                        'Pim\Behat\Decorator\Permission\PermissionDecorator'
                    ]
                ],
            ],
            $this->elements
        );
    }

    /**
     * Grant rights to all ACL resources
     */
    public function grantAllResourceRights()
    {
        $iconSelector = '.acl-permission .acl-permission-toggle.non-granted';

        $this->getSession()->executeScript(
            sprintf('$("%s").each(function () { $(this).click(); });', $iconSelector)
        );
    }

    /**
     * Grant or revoke rights to the given specified $group
     *
     * @param string $action 'grant'|'revoke'
     * @param string $group
     *
     * @throws \InvalidArgumentException
     */
    public function executeActionOnGroup($action, $group)
    {
        switch ($action) {
            case 'grant':
                $this->getElement('Role')->grantGroup($group);
                break;
            case 'revoke':
                $this->getElement('Role')->revokeGroup($group);
                break;
            default:
                throw new \InvalidArgumentException(sprintf('Action "%s" does not exist.', $action));
                break;
        }
    }

    /**
     * Grant or revoke a permission on the given resource
     *
     * @param string $action   'grant'|'revoke'
     * @param string $resource
     *
     * @throws \InvalidArgumentException
     */
    public function executeActionOnResource($action, $resource)
    {
        switch ($action) {
            case 'grant':
                $this->getElement('Role')->grantResource($resource);
                break;
            case 'revoke':
                $this->getElement('Role')->revokeResource($resource);
                break;
            default:
                throw new \InvalidArgumentException(sprintf('Action "%s" does not exist.', $action));
                break;
        }
    }
}
