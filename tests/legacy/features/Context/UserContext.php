<?php

declare(strict_types=1);

namespace Context;

use Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\RoleWithPermissionsRepository;
use Akeneo\UserManagement\Component\Connector\RoleWithPermissions;
use Akeneo\UserManagement\Component\Model\User;
use Akeneo\UserManagement\Component\Storage\Saver\RoleWithPermissionsSaver;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use Pim\Behat\Context\PimContext;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserContext extends PimContext
{
    private const LABEL_ACL_MAPPING = [
        'Manage Apps' => 'akeneo_connectivity_connection_manage_apps',
    ];

    /**
     * @Given the role :roleCode has the ACL :acl
     */
    public function iHaveTheAcl(string $roleCode, string $acl)
    {
        $acl = self::LABEL_ACL_MAPPING[$acl] ?? $acl;

        /** @var RoleWithPermissionsRepository $roleWithPermissionsRepository */
        $roleWithPermissionsRepository = $this->getService('pim_user.repository.role_with_permissions');
        /** @var AclManager $aclManager */
        $aclManager = $this->getService('oro_security.acl.manager');
        /** @var RoleWithPermissionsSaver $roleWithPermissionsSaver */
        $roleWithPermissionsSaver = $this->getService('pim_user.saver.role_with_permissions');

        /** @var RoleWithPermissions $roleWithPermissions */
        $roleWithPermissions = $roleWithPermissionsRepository->findOneByIdentifier($roleCode);
        if (null === $roleWithPermissions) {
            throw new \ErrorException("Role $roleCode not found");
        }

        $permissions = $roleWithPermissions->permissions();
        $permissions[sprintf('action:%s', $acl)] = true;
        $roleWithPermissions->setPermissions($permissions);

        $roleWithPermissionsSaver->saveAll([$roleWithPermissions]);

        $aclManager->flush();
        $aclManager->clearCache();
    }

    /**
     * @Given the user :username has the profile :profile
     */
    public function iHaveTheProfile(string $username, string $profile)
    {
        /** @var User $user */
        $user = $this
            ->getService('pim_user.repository.user')
            ->findOneBy(['username' => $username]);

        $user->setProfile($profile);

        $this->getService('pim_user.saver.user')->save($user);
    }
}
