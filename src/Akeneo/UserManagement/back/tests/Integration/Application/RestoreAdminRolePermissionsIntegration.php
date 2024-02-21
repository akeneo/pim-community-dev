<?php

namespace Akeneo\Test\UserManagement\Integration\Application;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\UserManagement\Application\Exception\UnknownUserRole;
use Akeneo\UserManagement\Application\RestoreAdminRolePermissions;
use Akeneo\UserManagement\Component\Connector\RoleWithPermissions;
use Webmozart\Assert\Assert;

class RestoreAdminRolePermissionsIntegration extends TestCase
{
    public function testItRestoresAllPermissions(): void
    {
        $this->removeAdminPermissions();

        $adminRole = $this->getAdminRoleWithPermissions();

        Assert::allFalse($adminRole->permissions());

        ($this->get(RestoreAdminRolePermissions::class))(false);

        $restoredAdminRole = $this->getAdminRoleWithPermissions();

        Assert::allTrue($restoredAdminRole->permissions());

    }

    public function testItCreateRoleWithAllPermissionsWhenNotExistAndCreationForced(): void
    {
        $this->removeAdminRole();

        $adminRole = $this->getAdminRoleWithPermissions();

        Assert::null($adminRole);

        ($this->get(RestoreAdminRolePermissions::class))(true);

        $restoredAdminRole = $this->getAdminRoleWithPermissions();

        Assert::isInstanceOf($restoredAdminRole, RoleWithPermissions::class);
        Assert::allTrue($restoredAdminRole->permissions());
    }

    public function testItFailsWhenRoleNotExistAndCreationNotForced(): void
    {
        $this->removeAdminRole();

        $this->expectException(UnknownUserRole::class);

        ($this->get(RestoreAdminRolePermissions::class))(false);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function removeAdminPermissions(): void
    {
        $roleWithPermissions = $this->getAdminRoleWithPermissions();
        $permissions = $roleWithPermissions->permissions();
        $revokedPermissions = [];

        foreach ($permissions as $acl => $isGranted) {
            $revokedPermissions[$acl] = false;
        }

        $roleWithPermissions->setPermissions($revokedPermissions);

        $this->get('pim_user.saver.role_with_permissions')->saveAll([$roleWithPermissions]);

        $this->get('oro_security.acl.manager')->flush();
        $this->get('oro_security.acl.manager')->clearCache();
    }

    private function removeAdminRole(): void
    {
        $deleteSql = <<<SQL
            DELETE FROM `oro_access_role` WHERE role ='ROLE_ADMINISTRATOR'
        SQL;

        $this->get('database_connection')->executeQuery($deleteSql);
    }

    private function getAdminRoleWithPermissions(): ?RoleWithPermissions
    {
        return $this->get('pim_user.repository.role_with_permissions')->findOneByIdentifier('ROLE_ADMINISTRATOR');
    }
}
