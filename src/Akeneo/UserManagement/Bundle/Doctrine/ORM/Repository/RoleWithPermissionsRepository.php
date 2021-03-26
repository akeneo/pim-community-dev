<?php

declare(strict_types=1);

namespace Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository;

use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\UserManagement\Component\Connector\RoleWithPermissions;
use Akeneo\UserManagement\Component\Model\RoleInterface;
use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclPrivilegeRepository;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RoleWithPermissionsRepository implements IdentifiableObjectRepositoryInterface
{
    private const ACL_EXTENSION_KEY = 'action';
    private const ACL_PERMISSION = 'EXECUTE';

    private IdentifiableObjectRepositoryInterface $roleRepository;
    private AclManager $aclManager;

    public function __construct(IdentifiableObjectRepositoryInterface $roleRepository, AclManager $aclManager)
    {
        $this->roleRepository = $roleRepository;
        $this->aclManager = $aclManager;
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByIdentifier($identifier): ?RoleWithPermissions
    {
        $role = $this->roleRepository->findOneByIdentifier($identifier);
        if (null === $role) {
            return null;
        }

        return RoleWithPermissions::createFromRoleAndPermissions(
            $role,
            $this->getPermissions($role)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierProperties(): array
    {
        return $this->roleRepository->getIdentifierProperties();
    }

    private function getPermissions(RoleInterface $role): array
    {
        $permissions = [];
        $privileges = $this->aclManager->getPrivilegeRepository()->getPrivileges(
            $this->aclManager->getSid($role)
        );

        foreach ($privileges as $privilege) {
            if (self::ACL_EXTENSION_KEY !== $privilege->getExtensionKey() ||
                AclPrivilegeRepository::ROOT_PRIVILEGE_NAME === $privilege->getIdentity()->getName()) {
                continue;
            }
            $isGranted = false;
            foreach ($privilege->getPermissions() as $permission) {
                if (self::ACL_PERMISSION === $permission->getName() &&
                    AccessLevel::NONE_LEVEL !== $permission->getAccessLevel()) {
                    $isGranted = true;
                    break;
                }
            }

            $permissions[$privilege->getIdentity()->getId()] = $isGranted;
        }

        return $permissions;
    }
}
