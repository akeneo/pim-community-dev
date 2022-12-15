<?php

namespace Akeneo\Category\back\tests\Integration\Infrastructure\Migration;

use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ConnectorBundle\Doctrine\UnitOfWorkAndRepositoriesClearer;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\RoleWithPermissionsRepository;
use Akeneo\UserManagement\Component\Model\Role;
use Akeneo\UserManagement\Component\Storage\Saver\RoleWithPermissionsSaver;
use Doctrine\DBAL\Connection;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

class V20221214GiveNewCategoryAclToUserWithOldCategoryAclZddMigrationIntegration extends CategoryTestCase
{
    private AclManager $aclManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->aclManager = $this->get('oro_security.acl.manager');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function testItGivesNewAclsRightToUserWithLegacyRightsOnCategories()
    {
        $this->createRoleWithAcls('ROLE_WITH_PERMS', ['pim_api_overall_access', 'pim_api_asset_family_edit']);
        $this->createRoleWithAcls('ROLE_WITHOUT_PERMS', []);

        // The administrator has the ACLs by default, due to its mask on (root)
        $this->assertRoleAclsAreGranted('ROLE_ADMINISTRATOR', [
            'pim_api_asset_family_edit' => true,
            'pim_api_asset_family_list' => true,
            'pim_api_asset_edit' => true,
            'pim_api_asset_list' => true,
            'pim_api_asset_remove' => true,
        ]);
        // This role has only its initial ACLs
        $this->assertRoleAclsAreGranted('ROLE_WITH_PERMS', [
            'pim_api_asset_family_edit' => true,
            'pim_api_asset_family_list' => false,
            'pim_api_asset_edit' => false,
            'pim_api_asset_list' => false,
            'pim_api_asset_remove' => false,
        ]);
        // This role has no ACLs
        $this->assertRoleAclsAreGranted('ROLE_WITHOUT_PERMS', [
            'pim_api_asset_family_edit' => false,
            'pim_api_asset_family_list' => false,
            'pim_api_asset_edit' => false,
            'pim_api_asset_list' => false,
            'pim_api_asset_remove' => false,
        ]);

        $this->reExecuteMigration($this->getMigrationLabel());

        // Destroying the Kernel is the only solution I found to properly purge stateful variables
        // from symfony/security-acl.
        $this->testKernel = static::bootKernel(['debug' => false]);

        // After the migration, the new ACLs can be found in the database
        $this->assertRoleAclsAreStoredInTheDatabase('ROLE_WITH_PERMS', [
            'pim_api_asset_family_edit' => true,
            'pim_api_asset_family_list' => true,
            'pim_api_asset_edit' => true,
            'pim_api_asset_list' => true,
            'pim_api_asset_remove' => true,
        ]);
        $this->assertRoleAclsAreStoredInTheDatabase('ROLE_WITHOUT_PERMS', [
            'pim_api_asset_family_edit' => true,
            'pim_api_asset_family_list' => true,
            'pim_api_asset_edit' => true,
            'pim_api_asset_list' => true,
            'pim_api_asset_remove' => true,
        ]);

        // After the migration, the administrator still has all the ACLs
        $this->assertRoleAclsAreGranted('ROLE_ADMINISTRATOR', [
            'pim_api_asset_family_edit' => true,
            'pim_api_asset_family_list' => true,
            'pim_api_asset_edit' => true,
            'pim_api_asset_list' => true,
            'pim_api_asset_remove' => true,
        ]);
        // After the migration, this role has the new ACLs while keeping the initial ones
        $this->assertRoleAclsAreGranted('ROLE_WITH_PERMS', [
            'pim_api_asset_family_edit' => true,
            'pim_api_asset_family_list' => true,
            'pim_api_asset_edit' => true,
            'pim_api_asset_list' => true,
            'pim_api_asset_remove' => true,
        ]);
        // After the migration, this role has only the new ACLs
        $this->assertRoleAclsAreGranted('ROLE_WITHOUT_PERMS', [
            'pim_api_asset_family_edit' => true,
            'pim_api_asset_family_list' => true,
            'pim_api_asset_edit' => true,
            'pim_api_asset_list' => true,
            'pim_api_asset_remove' => true,
        ]);
    }

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

    private function assertRoleAclsAreGranted(string $role, array $acls): void
    {
        /** @var AccessDecisionManagerInterface $decisionManager */
        $decisionManager = $this->get('security.access.decision_manager');
        $token = new UsernamePasswordToken('username', 'main', [$role]);

        foreach ($acls as $acl => $expectedValue) {
            assert(is_bool($expectedValue));

            $isAllowed = $decisionManager->decide($token, ['EXECUTE'], new ObjectIdentity('action', $acl));
            $this->assertEquals($expectedValue, $isAllowed, sprintf('%s %s', $role, $acl));
        }
    }

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

            $isAllowed = (boolean) $connection->fetchColumn($query, [
                'role' => $role,
                'acl' => $acl,
            ]);
            $this->assertEquals($expectedValue, $isAllowed);
        }
    }
}
