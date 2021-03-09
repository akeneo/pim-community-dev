<?php

declare(strict_types=1);

namespace Akeneo\UserManagement\Component\Updater;

use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Tool\Component\StorageUtils\Exception\UnknownPropertyException;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Component\Connector\RoleWithPermissions;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclPrivilegeRepository;
use Webmozart\Assert\Assert;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class RoleWithPermissionsUpdater implements ObjectUpdaterInterface
{
    private const ACL_DEFAULT_EXTENSION = 'action';

    private ObjectUpdaterInterface $roleUpdater;
    private AclManager $aclManager;

    public function __construct(ObjectUpdaterInterface $roleUpdater, AclManager $aclManager)
    {
        $this->roleUpdater = $roleUpdater;
        $this->aclManager = $aclManager;
    }

    public function update($roleWithPermissions, array $data, array $options = []): self
    {
        Assert::isInstanceOf($roleWithPermissions, RoleWithPermissions::class);
        foreach ($data as $property => $value) {
            switch ($property) {
                case 'role':
                case 'label':
                    $this->roleUpdater->update($roleWithPermissions->role(), [$property => $value]);
                    break;
                case 'permissions':
                    if (!\is_array($value)) {
                        throw InvalidPropertyTypeException::arrayExpected($property, self::class, $value);
                    }
                    $this->setPermissions($roleWithPermissions, $value);
                    break;
                default:
                    throw UnknownPropertyException::unknownProperty($property);
            }
        }

        return $this;
    }

    private function setPermissions(RoleWithPermissions $roleWithPermissions, array $grantedPermissions): void
    {
        $privileges = [];
        $aclPrivileges = $this->aclManager->getPrivilegeRepository()->getPrivileges(
            $this->aclManager->getSid($roleWithPermissions->role())
        );

        foreach ($aclPrivileges as $privilege) {
            if (self::ACL_DEFAULT_EXTENSION !== $privilege->getExtensionKey() ||
                $privilege->getIdentity()->getName() === AclPrivilegeRepository::ROOT_PRIVILEGE_NAME) {
                continue;
            }
            $privileges[$privilege->getIdentity()->getId()] = false;
        }
        foreach ($grantedPermissions as $grantedPermission) {
            $privileges[$grantedPermission] = true;
        }

        $roleWithPermissions->setPermissions($privileges);
    }
}
