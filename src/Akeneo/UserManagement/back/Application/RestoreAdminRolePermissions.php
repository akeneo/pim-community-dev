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
    public const ROLE_ADMINISTRATOR_IDENTIFIER = 'ROLE_ADMINISTRATOR';

    public function __construct(
        private AclManager $aclManager,
        private RoleWithPermissionsFactory $roleWithPermissionsFactory,
        private RoleWithPermissionsRepository $roleWithPermissionsRepository,
        private RoleWithPermissionsSaver $roleWithPermissionsSaver,
    ) {
    }

    public function __invoke(bool $forceCreation): void
    {
        $roleWithPermissions = $this->findOrCreateRole($forceCreation);
        $permissions = $roleWithPermissions->permissions();

        $restoredPermissions = [];
        foreach ($permissions as $acl => $isGranted) {
            $restoredPermissions[$acl] = true;
        }

        $roleWithPermissions->setPermissions($restoredPermissions);

        $this->roleWithPermissionsSaver->saveAll([$roleWithPermissions]);

        $this->aclManager->flush();
        $this->aclManager->clearCache();
    }

    private function findOrCreateRole(bool $forceCreation): RoleWithPermissions
    {
        $roleIdentifier = self::ROLE_ADMINISTRATOR_IDENTIFIER
        ;
        $roleWithPermissions = $this->roleWithPermissionsRepository->findOneByIdentifier($roleIdentifier);
        if (null === $roleWithPermissions) {
            if (!$forceCreation) {
                throw new UnknownUserRole(sprintf('The "%s" user role does not exist', $roleIdentifier));
            }

            $roleWithPermissions = $this->createRole($roleIdentifier);

            // Save and reload the Role to ensure to apply all the permissions (even Web API)
            $this->roleWithPermissionsSaver->saveAll([$roleWithPermissions]);
            $roleWithPermissions = $this->roleWithPermissionsRepository->findOneByIdentifier($roleIdentifier);
        }

        return $roleWithPermissions;
    }

    private function createRole(string $roleIdentifier): RoleWithPermissions
    {
        $roleWithPermissions = $this->roleWithPermissionsFactory->create();
        $roleWithPermissions->role()->setRole($roleIdentifier);
        $roleWithPermissions->role()->setLabel($roleIdentifier);

        return $roleWithPermissions;
    }
}
