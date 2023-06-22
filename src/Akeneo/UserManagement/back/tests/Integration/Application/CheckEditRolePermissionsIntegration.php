<?php

namespace Akeneo\Test\UserManagement\Integration\Application;

use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\Enrichment\UserLoader;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\ConnectorBundle\Doctrine\UnitOfWorkAndRepositoriesClearer;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\UserManagement\Application\CheckEditRolePermissions;
use Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\RoleWithPermissionsRepository;
use Akeneo\UserManagement\Component\Storage\Saver\RoleWithPermissionsSaver;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

class CheckEditRolePermissionsIntegration extends TestCase
{
    private CheckEditRolePermissions $checkEditRolePermissions;
    private RoleWithPermissionsRepository $roleWithPermissionsRepository;
    private UserLoader $userLoader;

    private AclManager $aclManager;
    private UnitOfWorkAndRepositoriesClearer $cacheClearer;
    private SimpleFactoryInterface $roleFactory;
    private SaverInterface $roleSaver;
    private RoleWithPermissionsSaver $roleWithPermissionsSaver;
    private AccessDecisionManagerInterface $decisionManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->checkEditRolePermissions = $this->get(CheckEditRolePermissions::class);
        $this->roleWithPermissionsRepository = $this->get('pim_user.repository.role_with_permissions');
        $this->userLoader = $this->get(UserLoader::class);
        $this->aclManager = $this->get('oro_security.acl.manager');
        $this->cacheClearer = $this->get('pim_connector.doctrine.cache_clearer');
        $this->roleFactory = $this->get('pim_user.factory.role');
        $this->roleSaver = $this->get('pim_user.saver.role');
        $this->roleWithPermissionsSaver = $this->get('pim_user.saver.role_with_permissions');
        $this->decisionManager = $this->get('security.access.decision_manager');

        $this->createRoleWithAcls('ROLE_WITH_EDIT_ROLE', CheckEditRolePermissions::MINIMUM_EDITROLE_PRIVILEGES);
        $this->createRoleWithAcls('ROLE_WITHOUT_EDIT_ROLE', ['action:oro_config_system']);
    }


    public function testItGetRolesWithEditRolePermissions(): void
    {
        $editRoleRoles =$this->checkEditRolePermissions->getRolesWithMinimumEditRolePrivileges();
        foreach ($editRoleRoles as $editRole) {
            $this->assertRoleAclsAreGranted($editRole->getRole(), [
                'pim_user_role_edit' => true,
                'pim_user_role_index' => true,
                'oro_config_system' => true,
            ]);
        }
    }

    public function testItGetUsersWithEditRolePermissions(): void
    {
        $userWithEditRole = $this->userLoader->createUser('userWithEditRole', [], ['ROLE_WITH_EDIT_ROLE']);
        $editRoleUsers = $this->checkEditRolePermissions->getUsersWithEditRoleRoles();
        $editUserFound = array_filter($editRoleUsers, fn($user) => $user === $userWithEditRole);
        $this::assertNotEmpty($editUserFound);
    }

    public function testItIsLastUserWithEditRolePermissions(): void
    {
        $userWithEditRole = $this->userLoader->createUser('userWithEditRole', [], ['ROLE_WITH_EDIT_ROLE']);
        $isLastEditRoleUser = $this->checkEditRolePermissions->isLastUserWithEditPrivilegeRole(['ROLE_WITHOUT_EDIT_ROLE'], $userWithEditRole->getId());
        $this::assertTrue($isLastEditRoleUser);
    }

    public function testItIsntLastUserWithEditRolePermissions(): void
    {
        $userWithEditRole = $this->userLoader->createUser('userWithEditRole', [], ['ROLE_WITH_EDIT_ROLE']);
        $this->userLoader->createUser('userWithEditRole2', [], ['ROLE_WITH_EDIT_ROLE']);
        $isLastEditRoleUser = $this->checkEditRolePermissions->isLastUserWithEditPrivilegeRole(['ROLE_WITHOUT_EDIT_ROLE'], $userWithEditRole->getId());
        $this::assertFalse($isLastEditRoleUser);
    }

    public function testThereIsNoUserWithEditRolePermissionsLeft(): void
    {
        $userWithoutEditRole = $this->userLoader->createUser('userWithoutEditRole', [], ['ROLE_WITHOUT_EDIT_ROLE']);
        $isLastEditRoleUser = $this->checkEditRolePermissions->isLastUserWithEditPrivilegeRole(['ROLE_WITHOUT_EDIT_ROLE'], $userWithoutEditRole->getId());
        $this::assertFalse($isLastEditRoleUser);
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

    private function assertRoleAclsAreGranted(string $role, array $acls): void
    {
        $token = new UsernamePasswordToken('username', 'main', [$role]);

        foreach ($acls as $acl => $expectedValue) {
            assert(is_bool($expectedValue));

            $isAllowed = $this->decisionManager->decide($token, ['EXECUTE'], new ObjectIdentity('action', $acl));
            $this->assertEquals($expectedValue, $isAllowed, sprintf('%s %s', $role, $acl));
        }
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
