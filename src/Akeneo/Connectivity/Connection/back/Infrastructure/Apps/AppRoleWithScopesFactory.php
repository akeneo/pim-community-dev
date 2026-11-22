<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps;

use Akeneo\Connectivity\Connection\Application\Apps\AppRoleWithScopesFactoryInterface;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\Query\IncreaseLabelLengthQuery;
use Akeneo\Connectivity\Connection\Infrastructure\Apps\Security\ScopeMapperRegistry;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\UserManagement\Component\Connector\RoleWithPermissions;
use Akeneo\UserManagement\Component\Model\RoleInterface;
use Akeneo\UserManagement\Component\Storage\Saver\RoleWithPermissionsSaver;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AppRoleWithScopesFactory implements AppRoleWithScopesFactoryInterface
{
    private const APP_ROLE_TYPE = 'app';

    private ScopeMapperRegistry $scopeMapperRegistry;
    private SimpleFactoryInterface $roleFactory;
    private RoleWithPermissionsSaver $roleWithPermissionsSaver;
    // Pull-up master: do not keep this property
    private IncreaseLabelLengthQuery $increaseLabelLengthQuery;

    public function __construct(
        ScopeMapperRegistry $scopeMapperRegistry,
        SimpleFactoryInterface $roleFactory,
        RoleWithPermissionsSaver $roleWithPermissionsSaver,
        // Pull-up master: do not keep this property
        ?IncreaseLabelLengthQuery $increaseLabelLengthQuery = null
    ) {
        $this->scopeMapperRegistry = $scopeMapperRegistry;
        $this->roleFactory = $roleFactory;
        $this->roleWithPermissionsSaver = $roleWithPermissionsSaver;
        // Pull-up master: do not keep this property
        $this->increaseLabelLengthQuery = $increaseLabelLengthQuery;
    }

    public function createRole(string $label, array $scopes): RoleInterface
    {
        /**
         * Pull-up master: remove the call to `increaseScopeLength()`. It's a workaround to not
         * create a migration on a released version.
         */
        if (null !== $this->increaseLabelLengthQuery) {
            $this->increaseLabelLengthQuery->execute();
        }

        /** @var RoleInterface $role */
        $role = $this->roleFactory->create();
        $role->setRole($this->createRandomRoleCode());
        $role->setLabel($label);
        $role->setType(self::APP_ROLE_TYPE);

        $permissions = [
            'action:pim_api_overall_access' => true,
        ];

        $acls = $this->scopeMapperRegistry->getAcls($scopes);
        foreach ($acls as $acl) {
            $permissions[\sprintf('action:%s', $acl)] = true;
        }

        $roleWithPermissions = RoleWithPermissions::createFromRoleAndPermissions($role, $permissions);
        $this->roleWithPermissionsSaver->saveAll([$roleWithPermissions]);

        return $role;
    }

    private function createRandomRoleCode(): string
    {
        return self::randomBase36(25);
    }

    /**
     * base_convert() works on floats for large numbers: on a 256 or 128-bit input it silently loses
     * most of the entropy (and PHP 8.6 raises a notice), so we draw each base36 character directly.
     */
    private static function randomBase36(int $length): string
    {
        $alphabet = '0123456789abcdefghijklmnopqrstuvwxyz';
        $result = '';
        for ($i = 0; $i < $length; $i++) {
            $result .= $alphabet[\random_int(0, 35)];
        }

        return $result;
    }
}
