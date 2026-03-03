<?php

namespace Akeneo\Test\UserManagement\Integration\Domain\Permissions;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\ConnectorBundle\Doctrine\UnitOfWorkAndRepositoriesClearer;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\RoleWithPermissionsRepository;
use Akeneo\UserManagement\Component\Storage\Saver\RoleWithPermissionsSaver;
use Akeneo\UserManagement\Domain\Permissions\MinimumEditRolePermission;
use Akeneo\UserManagement\Domain\Permissions\Query\EditRolePermissionsRoleQuery;
use Doctrine\DBAL\Connection;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

class EditRolePermissionsRoleQueryIntegration extends TestCase
{
    private EditRolePermissionsRoleQuery $editRolePermissionsRoleQuery;
    private RoleWithPermissionsRepository $roleWithPermissionsRepository;
    private AccessDecisionManagerInterface $decisionManager;
    private SimpleFactoryInterface $roleFactory;
    private SaverInterface $roleSaver;
    private RoleWithPermissionsSaver $roleWithPermissionsSaver;
    private AclManager $aclManager;
    private UnitOfWorkAndRepositoriesClearer $cacheClearer;
    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->editRolePermissionsRoleQuery = $this->get(EditRolePermissionsRoleQuery::class);
        $this->roleWithPermissionsRepository = $this->get('pim_user.repository.role_with_permissions');
        $this->decisionManager = $this->get('security.access.decision_manager');
        $this->roleFactory = $this->get('pim_user.factory.role');
        $this->roleSaver = $this->get('pim_user.saver.role');
        $this->roleWithPermissionsSaver = $this->get('pim_user.saver.role_with_permissions');
        $this->aclManager = $this->get('oro_security.acl.manager');
        $this->cacheClearer = $this->get('pim_connector.doctrine.cache_clearer');
        $this->connection = $this->get('database_connection');
    }


    public function testItGetRolesWithEditRolePermissions(): void
    {
        $editRoleRoles = $this->editRolePermissionsRoleQuery->getRolesWithMinimumEditRolePermissions();
        foreach ($editRoleRoles as $editRole) {
            $this->assertRoleAclsAreGranted($editRole->getRole(), [
                'pim_user_role_edit' => true,
                'pim_user_role_index' => true,
                'oro_config_system' => true,
            ]);
        }
    }

    public function testItIsLastRoleWithEditRolePermissions(): void
    {
        $this->createRoleWithAcls('ROLE_WITH_EDIT_ROLE', MinimumEditRolePermission::getAllValues());
        $this->deleteAllOtherRoles(['ROLE_WITH_EDIT_ROLE']);
        $this->assertTrue($this->editRolePermissionsRoleQuery->isLastRoleWithEditRolePermissions('ROLE_WITH_EDIT_ROLE'));
    }

    private function assertRoleAclsAreGranted(string $role, array $acls): void
    {
        $token = new UsernamePasswordToken('username', 'main', [$role]);

        foreach ($acls as $acl => $expectedValue) {
            assert(is_bool($expectedValue));

            $isAllowed = $this->decisionManager->decide($token, ['EXECUTE'], new ObjectIdentity('action', $acl));
            $this->assertEquals($expectedValue, $isAllowed, sprintf('%s %s', $role, $acl));
        }
    }

    private function createRoleWithAcls(string $roleCode, array $acls): void
    {
        $role = $this->roleFactory->create();
        $role->setRole($roleCode);
        $role->setLabel($roleCode);
        $this->roleSaver->save($role);

        $roleWithPermissions = $this->roleWithPermissionsRepository->findOneByIdentifier($roleCode);
        assert(null !== $roleWithPermissions);

        $permissions = $roleWithPermissions->permissions();
        foreach ($acls as $acl) {
            $permissions[$acl] = true;
        }
        $roleWithPermissions->setPermissions($permissions);

        $this->roleWithPermissionsSaver->saveAll([$roleWithPermissions]);

        $this->aclManager->flush();
        $this->aclManager->clearCache();
        $this->cacheClearer->clear();
    }

    /**
     * @param array<string> $roles
     */
    private function deleteAllOtherRoles(array $roles)
    {
        $this->connection->executeQuery(
            'DELETE FROM oro_access_role WHERE role NOT IN (:roles)',
            ['roles' => $roles],
            ['roles' => Connection::PARAM_STR_ARRAY]
        );
    }
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
