<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Version_7_0_20230110161438_initialize_identifier_generator_acl_Integration extends TestCase
{
    private const MIGRATION_NAME = '_7_0_20230110161438_initialize_identifier_generator_acl';
    private const PERMISSION_IDENTIFIER_GENERATOR_VIEW = 'action:pim_identifier_generator_view';
    private const PERMISSION_IDENTIFIER_GENERATOR_MANAGE = 'action:pim_identifier_generator_manage';

    use ExecuteMigrationTrait;

    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
    }

    public function test_it_adds_the_default_acl_values(): void
    {
        $this->get('feature_flags')->enable('identifier_generator');

        $roleFactory = $this->get('pim_user.factory.role');
        $roleSaver = $this->get('pim_user.saver.role');
        $aclManager = $this->get('oro_security.acl.manager');
        $cacheClearer = $this->get('pim_connector.doctrine.cache_clearer');

        // In CE, the Rule Engine ACLs doesn't exist, so we just create an empty role
        $roleCode = 'my_role';
        $role = $roleFactory->create();
        $role->setRole($roleCode);
        $role->setLabel($roleCode);
        $roleSaver->save($role);

        $roleWithPermissionsRepository = $this->get('pim_user.repository.role_with_permissions');
        $roleWithPermissions = $roleWithPermissionsRepository->findOneByIdentifier($roleCode);
        $permissions = $roleWithPermissions->permissions();

        // By default, none of the ACLs are granted
        Assert::assertArrayHasKey(self::PERMISSION_IDENTIFIER_GENERATOR_VIEW, $permissions);
        Assert::assertFalse($permissions[self::PERMISSION_IDENTIFIER_GENERATOR_VIEW]);
        Assert::assertArrayHasKey(self::PERMISSION_IDENTIFIER_GENERATOR_MANAGE, $permissions);
        Assert::assertFalse($permissions[self::PERMISSION_IDENTIFIER_GENERATOR_MANAGE]);

        $this->reExecuteMigration(self::MIGRATION_NAME);

        $aclManager->clearCache();
        $cacheClearer->clear();

        $roleWithPermissionsRepository = $this->get('pim_user.repository.role_with_permissions');
        $roleWithPermissions = $roleWithPermissionsRepository->findOneByIdentifier($roleCode);
        $permissions = $roleWithPermissions->permissions();

        // After the migration, the user should have permission to view, but not manage
        Assert::assertArrayHasKey(self::PERMISSION_IDENTIFIER_GENERATOR_VIEW, $permissions);
        Assert::assertTrue($permissions[self::PERMISSION_IDENTIFIER_GENERATOR_VIEW]);
        Assert::assertArrayHasKey(self::PERMISSION_IDENTIFIER_GENERATOR_MANAGE, $permissions);
        Assert::assertFalse($permissions[self::PERMISSION_IDENTIFIER_GENERATOR_MANAGE]);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
