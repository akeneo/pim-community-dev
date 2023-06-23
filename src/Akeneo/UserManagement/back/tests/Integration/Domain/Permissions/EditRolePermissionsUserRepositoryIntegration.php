<?php

namespace Akeneo\Test\UserManagement\Integration\Domain\Permissions;

use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\Enrichment\UserLoader;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\ConnectorBundle\Doctrine\UnitOfWorkAndRepositoriesClearer;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\RoleWithPermissionsRepository;
use Akeneo\UserManagement\Component\Storage\Saver\RoleWithPermissionsSaver;
use Akeneo\UserManagement\Domain\Permissions\EditRolePermissionsUserRepository;
use Akeneo\UserManagement\Domain\Permissions\MinimumEditRolePermission;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;

class EditRolePermissionsUserRepositoryIntegration extends TestCase
{
    private EditRolePermissionsUserRepository $editRolePermissionsUserRepository;
    private RoleWithPermissionsRepository $roleWithPermissionsRepository;
    private UserLoader $userLoader;

    private AclManager $aclManager;
    private UnitOfWorkAndRepositoriesClearer $cacheClearer;
    private SimpleFactoryInterface $roleFactory;
    private SaverInterface $roleSaver;
    private RoleWithPermissionsSaver $roleWithPermissionsSaver;
    protected function setUp(): void
    {
        parent::setUp();
        $this->editRolePermissionsUserRepository = $this->get(EditRolePermissionsUserRepository::class);
        $this->roleWithPermissionsRepository = $this->get('pim_user.repository.role_with_permissions');
        $this->userLoader = $this->get(UserLoader::class);
        $this->aclManager = $this->get('oro_security.acl.manager');
        $this->cacheClearer = $this->get('pim_connector.doctrine.cache_clearer');
        $this->roleFactory = $this->get('pim_user.factory.role');
        $this->roleSaver = $this->get('pim_user.saver.role');
        $this->roleWithPermissionsSaver = $this->get('pim_user.saver.role_with_permissions');

        $this->createRoleWithAcls('ROLE_WITH_EDIT_ROLE', MinimumEditRolePermission::getAllValues());
        $this->createRoleWithAcls('ROLE_WITHOUT_EDIT_ROLE', ['action:oro_config_system']);
    }

    public function testItGetUsersWithEditRolePermissions(): void
    {
        $userWithEditRole = $this->userLoader->createUser('userWithEditRole', [], ['ROLE_WITH_EDIT_ROLE']);
        $editRoleUsers = $this->editRolePermissionsUserRepository->getUsersWithEditRoleRoles();
        $editUserFound = array_filter($editRoleUsers, fn($user) => $user === $userWithEditRole);
        $this::assertNotEmpty($editUserFound);
    }

    public function testItIsLastRoleWithEditRolePermissionsForUser(): void
    {
        $userWithEditRole = $this->userLoader->createUser('userWithEditRole', [], ['ROLE_WITH_EDIT_ROLE']);
        $isLastEditRoleUser = $this->editRolePermissionsUserRepository->isLastRoleWithEditRolePermissionsRoleForUser(['ROLE_WITHOUT_EDIT_ROLE'], $userWithEditRole->getId());
        $this::assertTrue($isLastEditRoleUser);
    }

    public function testItIsntLastRoleWithEditRolePermissionsForUser(): void
    {
        $userWithEditRole = $this->userLoader->createUser('userWithEditRole', [], ['ROLE_WITH_EDIT_ROLE']);
        $this->userLoader->createUser('userWithEditRole2', [], ['ROLE_WITH_EDIT_ROLE']);
        $isLastEditRoleUser = $this->editRolePermissionsUserRepository->isLastRoleWithEditRolePermissionsRoleForUser(['ROLE_WITHOUT_EDIT_ROLE'], $userWithEditRole->getId());
        $this::assertFalse($isLastEditRoleUser);
    }

    public function testItIsLastUserWithEditRolePermissions(): void
    {
        $userWithEditRole = $this->userLoader->createUser('userWithEditRole', [], ['ROLE_WITH_EDIT_ROLE']);
        $isLastEditRoleUser = $this->editRolePermissionsUserRepository->isLastUserWithEditRolePermissionsRole(['ROLE_WITHOUT_EDIT_ROLE'], $userWithEditRole->getId());
        $this::assertTrue($isLastEditRoleUser);
    }

    public function testItIsntLastUserWithEditRolePermissions(): void
    {
        $userWithEditRole = $this->userLoader->createUser('userWithEditRole', [], ['ROLE_WITH_EDIT_ROLE']);
        $this->userLoader->createUser('userWithEditRole2', [], ['ROLE_WITH_EDIT_ROLE']);
        $isLastEditRoleUser = $this->editRolePermissionsUserRepository->isLastUserWithEditRolePermissionsRole(['ROLE_WITHOUT_EDIT_ROLE'], $userWithEditRole->getId());
        $this::assertFalse($isLastEditRoleUser);
    }

    public function testThereIsNoUserWithEditRolePermissionsLeft(): void
    {
        $userWithoutEditRole = $this->userLoader->createUser('userWithoutEditRole', [], ['ROLE_WITHOUT_EDIT_ROLE']);
        $isLastEditRoleUser = $this->editRolePermissionsUserRepository->isLastUserWithEditRolePermissionsRole(['ROLE_WITHOUT_EDIT_ROLE'], $userWithoutEditRole->getId());
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
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
