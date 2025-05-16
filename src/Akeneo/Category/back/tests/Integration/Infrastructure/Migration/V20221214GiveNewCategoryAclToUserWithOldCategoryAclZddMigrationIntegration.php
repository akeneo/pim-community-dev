<?php

namespace Akeneo\Category\tests\Integration\Infrastructure\Migration;

use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;
use Akeneo\Category\Infrastructure\Migration\V20221214GiveNewCategoryAclToUserWithOldCategoryAclZddMigration;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ConnectorBundle\Doctrine\UnitOfWorkAndRepositoriesClearer;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\RoleWithPermissionsRepository;
use Akeneo\UserManagement\Component\Model\Role;
use Akeneo\UserManagement\Component\Model\User;
use Akeneo\UserManagement\Component\Storage\Saver\RoleWithPermissionsSaver;
use Doctrine\DBAL\Connection;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

class V20221214GiveNewCategoryAclToUserWithOldCategoryAclZddMigrationIntegration extends CategoryTestCase
{
    private V20221214GiveNewCategoryAclToUserWithOldCategoryAclZddMigration $migration;

    protected function setUp(): void
    {
        parent::setUp();
        $this->migration = $this->get(V20221214GiveNewCategoryAclToUserWithOldCategoryAclZddMigration::class);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function testMigrationGivesACLToEditEnrichedCategoryWhenUserHasACLToEditCategory(): void
    {
        $this->createRoleWithAcls('ROLE_WITH_CATEGORY_EDIT_PERMS', ['pim_enrich_product_category_edit']);

        // This role has only its initial ACLs
        $this->assertRoleAclsAreGranted('ROLE_WITH_CATEGORY_EDIT_PERMS', [
            'pim_enrich_product_category_list' => false,
            'pim_enrich_product_category_create' => false,
            'pim_enrich_product_category_edit' => true,
            'pim_enrich_product_category_remove' => false,
            'pim_enrich_product_category_history' => false,
            // these are the new ACLs
            'pim_enrich_product_category_template' => false,
            'pim_enrich_product_category_edit_attributes' => false,
            'pim_enrich_product_category_order_trees' => false,
        ]);

        $this->migration->migrate();
        // Destroying the Kernel to properly purge stateful variables from symfony/security-acl.
        $this->testKernel = static::bootKernel(['debug' => false]);

        $this->assertRoleAclsAreStoredInTheDatabase('ROLE_WITH_CATEGORY_EDIT_PERMS', [
            'pim_enrich_product_category_edit' => true,
            // these are the new ACLs
            'pim_enrich_product_category_template' => true,
            'pim_enrich_product_category_edit_attributes' => true,
            'pim_enrich_product_category_order_trees' => true,
        ]);

        // After the migration, this role has the new ACLs while keeping the initial ones
        $this->assertRoleAclsAreGranted('ROLE_WITH_CATEGORY_EDIT_PERMS', [
            'pim_enrich_product_category_list' => false,
            'pim_enrich_product_category_create' => false,
            'pim_enrich_product_category_edit' => true,
            'pim_enrich_product_category_remove' => false,
            'pim_enrich_product_category_history' => false,
            // these are the new ACLs
            'pim_enrich_product_category_template' => true,
            'pim_enrich_product_category_edit_attributes' => true,
            'pim_enrich_product_category_order_trees' => true,
        ]);
    }

    public function testMigrationGivesACLToEditCategoryTemplateWhenUserHasACLToCreateCategory(): void
    {
        $this->createRoleWithAcls('ROLE_WITH_CATEGORY_CREATE_PERMS', ['pim_enrich_product_category_create']);

        // This role has only its initial ACLs
        $this->assertRoleAclsAreGranted('ROLE_WITH_CATEGORY_CREATE_PERMS', [
            'pim_enrich_product_category_list' => false,
            'pim_enrich_product_category_create' => true,
            'pim_enrich_product_category_edit' => false,
            'pim_enrich_product_category_remove' => false,
            'pim_enrich_product_category_history' => false,
            // these are the new ACLs
            'pim_enrich_product_category_template' => false,
            'pim_enrich_product_category_edit_attributes' => false,
            'pim_enrich_product_category_order_trees' => false,
        ]);

        $this->migration->migrate();
        // Destroying the Kernel to properly purge stateful variables from symfony/security-acl.
        $this->testKernel = static::bootKernel(['debug' => false]);

        $this->assertRoleAclsAreStoredInTheDatabase('ROLE_WITH_CATEGORY_CREATE_PERMS', [
            'pim_enrich_product_category_create' => true,
            // these are the new ACLs
            'pim_enrich_product_category_template' => true,
            'pim_enrich_product_category_edit_attributes' => false,
            'pim_enrich_product_category_order_trees' => false,
        ]);
        // After the migration, this role has the new ACLs while keeping the initial ones
        $this->assertRoleAclsAreGranted('ROLE_WITH_CATEGORY_CREATE_PERMS', [
            'pim_enrich_product_category_list' => false,
            'pim_enrich_product_category_create' => true,
            'pim_enrich_product_category_edit' => false,
            'pim_enrich_product_category_remove' => false,
            'pim_enrich_product_category_history' => false,
            // these are the new ACLs
            'pim_enrich_product_category_template' => true,
            'pim_enrich_product_category_edit_attributes' => false,
            'pim_enrich_product_category_order_trees' => false,
        ]);
    }

    public function testMigrationGivesACLOnEnrichedCategoryOnlyToRolesWithACLToCreateAndEditCategory(): void
    {
        $this->createRoleWithAcls('ROLE_WITH_CATEGORY_PERMS', ['pim_enrich_product_category_edit','pim_enrich_product_category_create']);
        $this->createRoleWithAcls('ROLE_WITHOUT_CATEGORY_PERMS', []);

        // These roles have only their initial ACLs
        $this->assertRoleAclsAreGranted('ROLE_WITH_CATEGORY_PERMS', [
            'pim_enrich_product_category_list' => false,
            'pim_enrich_product_category_create' => true,
            'pim_enrich_product_category_edit' => true,
            'pim_enrich_product_category_remove' => false,
            'pim_enrich_product_category_history' => false,
            // these are the new ACLs
            'pim_enrich_product_category_template' => false,
            'pim_enrich_product_category_edit_attributes' => false,
            'pim_enrich_product_category_order_trees' => false,
        ]);
        $this->assertRoleAclsAreGranted('ROLE_WITHOUT_CATEGORY_PERMS', [
            'pim_enrich_product_category_list' => false,
            'pim_enrich_product_category_create' => false,
            'pim_enrich_product_category_edit' => false,
            'pim_enrich_product_category_remove' => false,
            'pim_enrich_product_category_history' => false,
            // these are the new ACLs
            'pim_enrich_product_category_template' => false,
            'pim_enrich_product_category_edit_attributes' => false,
            'pim_enrich_product_category_order_trees' => false,
        ]);


        $this->migration->migrate();
        // Destroying the Kernel to properly purge stateful variables from symfony/security-acl.
        $this->testKernel = static::bootKernel(['debug' => false]);

        $this->assertRoleAclsAreStoredInTheDatabase('ROLE_WITH_CATEGORY_PERMS', [
            'pim_enrich_product_category_list' => false,
            'pim_enrich_product_category_create' => true,
            'pim_enrich_product_category_edit' => true,
            'pim_enrich_product_category_remove' => false,
            'pim_enrich_product_category_history' => false,
            // these are the new ACLs
            'pim_enrich_product_category_template' => true,
            'pim_enrich_product_category_edit_attributes' => true,
            'pim_enrich_product_category_order_trees' => true,
        ]);
        $this->assertRoleAclsAreStoredInTheDatabase('ROLE_WITHOUT_CATEGORY_PERMS', [
            'pim_enrich_product_category_list' => false,
            'pim_enrich_product_category_create' => false,
            'pim_enrich_product_category_edit' => false,
            'pim_enrich_product_category_remove' => false,
            'pim_enrich_product_category_history' => false,
            // these are the new ACLs
            'pim_enrich_product_category_template' => false,
            'pim_enrich_product_category_edit_attributes' => false,
            'pim_enrich_product_category_order_trees' => false,
        ]);
        // After the migration, this role has the new ACLs while keeping the initial ones
        $this->assertRoleAclsAreGranted('ROLE_WITH_CATEGORY_PERMS', [
            'pim_enrich_product_category_list' => false,
            'pim_enrich_product_category_create' => true,
            'pim_enrich_product_category_edit' => true,
            'pim_enrich_product_category_remove' => false,
            'pim_enrich_product_category_history' => false,
            // these are the new ACLs
            'pim_enrich_product_category_template' => true,
            'pim_enrich_product_category_edit_attributes' => true,
            'pim_enrich_product_category_order_trees' => true,
        ]);
        // After the migration, this role has not the new ACLs, but it keeps the initial ones
        $this->assertRoleAclsAreGranted('ROLE_WITHOUT_CATEGORY_PERMS', [
            'pim_enrich_product_category_list' => false,
            'pim_enrich_product_category_create' => false,
            'pim_enrich_product_category_edit' => false,
            'pim_enrich_product_category_remove' => false,
            'pim_enrich_product_category_history' => false,
            // these are the new ACLs
            'pim_enrich_product_category_template' => false,
            'pim_enrich_product_category_edit_attributes' => false,
            'pim_enrich_product_category_order_trees' => false,
        ]);
    }

    /**
     * @param array<int, string> $acls
     */
    private function createRoleWithAcls(string $roleCode, array $acls): void
    {
        /** @var AclManager $aclManager */
        $aclManager = $this->get('oro_security.acl.manager');
        /** @var UnitOfWorkAndRepositoriesClearer $cacheClearer */
        $cacheClearer = $this->get('pim_connector.doctrine.cache_clearer');
        /** @var SimpleFactoryInterface $roleFactory */
        $roleFactory = $this->get('pim_user.factory.role');
        /** @var SaverInterface $roleSaver */
        $roleSaver = $this->get('pim_user.saver.role');
        /** @var RoleWithPermissionsRepository $roleWithPermissionsRepository */
        $roleWithPermissionsRepository = $this->get('pim_user.repository.role_with_permissions');
        /** @var RoleWithPermissionsSaver $roleWithPermissionsSaver */
        $roleWithPermissionsSaver = $this->get('pim_user.saver.role_with_permissions');

        /** @var Role $role */
        $role = $roleFactory->create();
        $role->setRole($roleCode);
        $role->setLabel($roleCode);
        $roleSaver->save($role);

        $roleWithPermissions = $roleWithPermissionsRepository->findOneByIdentifier($roleCode);
        assert(null !== $roleWithPermissions);

        $permissions = $roleWithPermissions->permissions();
        foreach ($acls as $acl) {
            $permissions[sprintf('action:%s', $acl)] = true;
        }
        $roleWithPermissions->setPermissions($permissions);

        $roleWithPermissionsSaver->saveAll([$roleWithPermissions]);

        $aclManager->flush();
        $aclManager->clearCache();
        $cacheClearer->clear();
    }

    /**
     * @param array<string, bool> $acls
     */
    private function assertRoleAclsAreGranted(string $role, array $acls): void
    {
        /** @var AccessDecisionManagerInterface $decisionManager */
        $decisionManager = $this->get('security.access.decision_manager');

        $user = $this->get('pim_user.repository.user')->findOneByIdentifier('my_user');
        if (empty($user)) {
            $user = $this->createUser('my_user', []);
        }
        $this->assertNotNull($user);

        $token = new UsernamePasswordToken($user, 'main', [$role]);

        foreach ($acls as $acl => $expectedValue) {
            assert(is_bool($expectedValue));

            $isAllowed = $decisionManager->decide($token, ['EXECUTE'], new ObjectIdentity('action', $acl));
            $this->assertEquals($expectedValue, $isAllowed, sprintf('Role %s should have ACL \'%s\' = %s', $role, $acl, ($isAllowed ? 'false' : 'true')));
        }
    }

    /**
     * @param array<string, bool> $acls
     */
    private function assertRoleAclsAreStoredInTheDatabase(string $role, array $acls): void
    {
        $query = <<<SQL
            SELECT acl_entries.granting
            FROM acl_entries
                LEFT JOIN acl_security_identities ON acl_security_identities.id = acl_entries.security_identity_id
                LEFT JOIN acl_classes ON acl_entries.class_id = acl_classes.id
            WHERE acl_security_identities.identifier = :role
                AND acl_classes.class_type = :acl
        SQL;

        /** @var Connection $connection */
        $connection = $this->get('database_connection');
        foreach ($acls as $acl => $expectedValue) {
            assert(is_bool($expectedValue));

            $isAllowed = (boolean) $connection->fetchOne($query, [
                'role' => $role,
                'acl' => $acl,
            ]);
            $this->assertEquals(
                $expectedValue,
                $isAllowed,
                sprintf('\'%s\' should be stored in database.', $acl)
            );
        }
    }

    /**
     * @param string $username
     * @param array<string> $groups
     * @return User
     */
    private function createUser(string $username, array $groups): User
    {
        $user = $this->get('pim_user.factory.user')->create();
        $user->setUsername($username);
        $user->setEmail(sprintf('%s@example.com', uniqid()));
        $user->setPassword('fake');

        foreach ($groups as $group) {
            $user->addGroup($group);
        }

        $roles = $this->get('pim_user.repository.role')->findAll();
        foreach ($roles as $role) {
            $user->addRole($role);
        }

        $this->get('pim_user.saver.user')->save($user);

        return $user;
    }
}
