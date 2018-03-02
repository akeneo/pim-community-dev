<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\UserBundle\Form\Handler;

use Pim\Bundle\UserBundle\Form\Handler\AclRoleHandler as OroAclRoleHandler;
use Pim\Component\User\Model\Role;
use PimEnterprise\Bundle\UserBundle\Form\Type\AclRoleType;

/**
 * Override from Pim\UserBundle to use the User override class in the EE.
 *
 * @author Clement Gautier <clement.gautier@akeneo.com>
 *
 * @deprecated To be removed when UserBundle from oro will be moved to Pim namespace
 */
class AclRoleHandler extends OroAclRoleHandler
{
    /**
     * {@inheritdoc}
     */
    public function createForm(Role $role)
    {
        foreach ($this->privilegeConfig as $configName => $config) {
            $this->privilegeConfig[$configName]['permissions'] = $this->aclManager
                ->getPrivilegeRepository()->getPermissionNames($config['types']);
        }

        $this->form = $this->formFactory->create(
            AclRoleType::class,
            $role,
            ['privilegeConfigOption' => $this->privilegeConfig]
        );

        return $this->form;
    }
}
