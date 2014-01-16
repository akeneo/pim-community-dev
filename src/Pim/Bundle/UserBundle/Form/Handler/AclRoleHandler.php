<?php

namespace Pim\Bundle\UserBundle\Form\Handler;

use Oro\Bundle\UserBundle\Entity\Role;
use Oro\Bundle\UserBundle\Form\Handler\AclRoleHandler as OroAclRoleHandler;
use Pim\Bundle\UserBundle\Form\Type\AclRoleType;

/**
 * Overriden AclRoleHandler to remove deactivated locales from the acl role form
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AclRoleHandler extends OroAclRoleHandler
{
    /**
     * Create form for role manipulation
     *
     * @param Role $role
     *
     * @return FormInterface
     */
    public function createForm(Role $role)
    {
        foreach ($this->privilegeConfig as $configName => $config) {
            $this->privilegeConfig[$configName]['permissions'] = $this->aclManager
                ->getPrivilegeRepository()->getPermissionNames($config['types']);
        }

        $this->form = $this->formFactory->create(
            new ACLRoleType(
                $this->privilegeConfig
            ),
            $role
        );

        return $this->form;
    }
}
