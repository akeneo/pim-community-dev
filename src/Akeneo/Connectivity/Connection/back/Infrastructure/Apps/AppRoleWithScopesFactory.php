<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps;

use Akeneo\Tool\Bundle\ApiBundle\Security\ScopeMapper;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\UserManagement\Component\Connector\RoleWithPermissions;
use Akeneo\UserManagement\Component\Model\RoleInterface;
use Akeneo\UserManagement\Component\Storage\Saver\RoleWithPermissionsSaver;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AppRoleWithScopesFactory
{
    private const APP_ROLE_TYPE = 'app';

    private ScopeMapper $scopeMapper;
    private SimpleFactoryInterface $roleFactory;
    private RoleWithPermissionsSaver $roleWithPermissionsSaver;

    public function __construct(
        ScopeMapper $scopeMapper,
        SimpleFactoryInterface $roleFactory,
        RoleWithPermissionsSaver $roleWithPermissionsSaver
    ) {
        $this->scopeMapper = $scopeMapper;
        $this->roleFactory = $roleFactory;
        $this->roleWithPermissionsSaver = $roleWithPermissionsSaver;
    }

    public function createRole(string $label, array $scopes): RoleInterface
    {
        /** @var RoleInterface $role */
        $role = $this->roleFactory->create();
        $role->setRole($this->createRandomRoleCode());
        $role->setLabel($label);
        $role->setType(self::APP_ROLE_TYPE);

        $permissions = [];

        foreach ($scopes as $scope) {
            $acls = $this->scopeMapper->getAcls($scope);

            foreach ($acls as $acl) {
                $permissions[sprintf('action:%s', $acl)] = true;
            }
        }

        $roleWithPermissions = RoleWithPermissions::createFromRoleAndPermissions($role, $permissions);
        $this->roleWithPermissionsSaver->saveAll([$roleWithPermissions]);

        return $role;
    }

    private function createRandomRoleCode(): string
    {
        return base_convert(bin2hex(random_bytes(16)), 16, 36);
    }
}
