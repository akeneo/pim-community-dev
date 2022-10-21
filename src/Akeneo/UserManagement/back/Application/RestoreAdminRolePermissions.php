<?php

namespace Akeneo\UserManagement\Application;

use Akeneo\UserManagement\Application\Exception\UnknownUserRole;
use Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\RoleWithPermissionsRepository;
use Akeneo\UserManagement\Component\Connector\RoleWithPermissions;
use Akeneo\UserManagement\Component\Factory\RoleWithPermissionsFactory;
use Akeneo\UserManagement\Component\Storage\Saver\RoleWithPermissionsSaver;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;

class RestoreAdminRolePermissions
{
    public function __construct(
        private AclManager $aclManager,
        private RoleWithPermissionsFactory $roleWithPermissionsFactory,
        private RoleWithPermissionsRepository $roleWithPermissionsRepository,
        private RoleWithPermissionsSaver $roleWithPermissionsSaver,
    )
    {

    }

    public function __invoke(bool $forceCreation = false): void
    {
        $roleIdentifier = 'ROLE_ADMINISTRATOR';
        $roleWithPermissions = $this->roleWithPermissionsRepository->findOneByIdentifier($roleIdentifier);
        if (null === $roleWithPermissions) {
            if (!$forceCreation) {
                throw new UnknownUserRole(sprintf('The "%s" user role does not exist', $roleIdentifier));
            }

            $roleWithPermissions = $this->createRole($roleIdentifier);
        }

        $permissions = $roleWithPermissions->permissions();
        foreach ($permissions as $aclName => $isEnabled) {
            $permissions[$aclName] = true;
        }
        $roleWithPermissions->setPermissions($permissions);

        $this->roleWithPermissionsSaver->saveAll([$roleWithPermissions]);

        $this->aclManager->flush();
        $this->aclManager->clearCache();
    }

    private function createRole(string $roleIdentifier): RoleWithPermissions
    {
        $roleWithPermissions = $this->roleWithPermissionsFactory->create();
        $roleWithPermissions->role()->setRole($roleIdentifier);
        $roleWithPermissions->role()->setLabel($roleIdentifier);


        return $roleWithPermissions;
    }
}
