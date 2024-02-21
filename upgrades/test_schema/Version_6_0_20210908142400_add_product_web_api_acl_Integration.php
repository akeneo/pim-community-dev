<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\ConnectorBundle\Doctrine\UnitOfWorkAndRepositoriesClearer;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\RoleWithPermissionsRepository;
use Akeneo\UserManagement\Component\Model\Role;
use Akeneo\UserManagement\Component\Storage\Saver\RoleWithPermissionsSaver;
use Doctrine\Common\Cache\CacheProvider;
use Doctrine\DBAL\Connection;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class Version_6_0_20210908142400_add_product_web_api_acl_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private AclManager $aclManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->aclManager = $this->get('oro_security.acl.manager');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    public function test_the_migration_adds_the_new_permissions_to_existing_roles()
    {
        $this->createRoleWithAcls('ROLE_WITH_PERMS', ['pim_api_overall_access', 'pim_api_product_list']);
        $this->createRoleWithAcls('ROLE_WITHOUT_PERMS', []);

        // The administrator has the ACLs by default, due to its mask on (root)
        $this->assertRoleAclsAreGranted('ROLE_ADMINISTRATOR', [
            'pim_api_product_list' => true,
            'pim_api_product_edit' => true,
            'pim_api_product_remove' => true,
        ]);
        // This role has only its initial ACLs
        $this->assertRoleAclsAreGranted('ROLE_WITH_PERMS', [
            'pim_api_overall_access' => true,
            'pim_api_product_list' => true,
            'pim_api_product_edit' => false,
            'pim_api_product_remove' => false,
        ]);
        // This role has no ACLs
        $this->assertRoleAclsAreGranted('ROLE_WITHOUT_PERMS', [
            'pim_api_overall_access' => false,
            'pim_api_product_list' => false,
            'pim_api_product_edit' => false,
            'pim_api_product_remove' => false,
        ]);

        $this->reExecuteMigration($this->getMigrationLabel());

        // Destroying the Kernel is the only solution I found to properly purge stateful variables
        // from symfony/security-acl.
        $this->testKernel = static::bootKernel(['debug' => false]);

        // After the migration, the new ACLs can be found in the database
        $this->assertRoleAclsAreStoredInTheDatabase('ROLE_WITH_PERMS', [
            'pim_api_product_list' => true,
            'pim_api_product_edit' => true,
            'pim_api_product_remove' => true,
        ]);
        $this->assertRoleAclsAreStoredInTheDatabase('ROLE_WITHOUT_PERMS', [
            'pim_api_product_list' => true,
            'pim_api_product_edit' => true,
            'pim_api_product_remove' => true,
        ]);

        // After the migration, the administrator still has all the ACLs
        $this->assertRoleAclsAreGranted('ROLE_ADMINISTRATOR', [
            'pim_api_product_list' => true,
            'pim_api_product_edit' => true,
            'pim_api_product_remove' => true,
        ]);

        // After the migration, this role has the new ACLs while keeping the initial ones
        $this->assertRoleAclsAreGranted('ROLE_WITH_PERMS', [
            'pim_api_overall_access' => true,
            'pim_api_product_list' => true,
            'pim_api_product_edit' => true,
            'pim_api_product_remove' => true,
        ]);
        // After the migration, this role has only the new ACLs
        $this->assertRoleAclsAreGranted('ROLE_WITHOUT_PERMS', [
            'pim_api_overall_access' => false,
            'pim_api_product_list' => true,
            'pim_api_product_edit' => true,
            'pim_api_product_remove' => true,
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

            $isAllowed = (boolean) $connection->fetchOne($query, [
                'role' => $role,
                'acl' => $acl,
            ]);
            $this->assertEquals($expectedValue, $isAllowed);
        }
    }

    private function getMigrationLabel(): string
    {
        $migration = (new \ReflectionClass($this))->getShortName();
        $migration = str_replace('_Integration', '', $migration);
        $migration = str_replace('Version', '', $migration);

        return $migration;
    }
}
