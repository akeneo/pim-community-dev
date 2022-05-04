<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\CatalogBuilder\Security;

use Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\RoleWithPermissionsRepository;
use Akeneo\UserManagement\Component\Storage\Saver\RoleWithPermissionsSaver;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AclLoader
{
    public function __construct(
        private AclManager $aclManager,
        private RoleWithPermissionsRepository $roleWithPermissionsRepository,
        private RoleWithPermissionsSaver $roleWithPermissionsSaver,
    ) {
    }

    public function addAclToRoles(string $acl, array $roles): void
    {
        $roleWithPermissionsCollection = [];
        foreach ($roles as $role) {
            $roleWithPermissions = $this->roleWithPermissionsRepository->findOneByIdentifier($role);
            \assert(null !== $roleWithPermissions);

            $permissions = $roleWithPermissions->permissions();
            $permissions[\sprintf('action:%s', $acl)] = true;
            $roleWithPermissions->setPermissions($permissions);

            $roleWithPermissionsCollection[] = $roleWithPermissions;
        }

        $this->roleWithPermissionsSaver->saveAll($roleWithPermissionsCollection);
        $this->aclManager->flush();
        $this->aclManager->clearCache();
    }
}
