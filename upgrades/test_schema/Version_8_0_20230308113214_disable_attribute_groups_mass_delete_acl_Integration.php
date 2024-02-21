<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\RoleWithPermissionsRepository;
use Doctrine\DBAL\Connection;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;

final class Version_8_0_20230308113214_disable_attribute_groups_mass_delete_acl_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_8_0_20230308113214_disable_attribute_groups_mass_delete_acl';
    private const ACL = 'pim_enrich_attributegroup_mass_delete';

    private Connection $connection;
    private AclManager $aclManager;
    private EntityManagerClearerInterface $cacheClearer;
    private RoleWithPermissionsRepository $roleWithPermissionsRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
        $this->aclManager = $this->get('oro_security.acl.manager');
        $this->cacheClearer = $this->get('pim_connector.doctrine.cache_clearer');
        $this->roleWithPermissionsRepository = $this->get('pim_user.repository.role_with_permissions');
    }

    public function testItDisablesAcl(): void
    {
        $this->reExecuteMigration(self::MIGRATION_LABEL);
        $this->aclManager->clearCache();
        $this->cacheClearer->clear();

        $this->assertAclIsDisabled();
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function assertAclIsDisabled(): void
    {
        $roles = $this->getRoles();
        foreach ($roles as $role) {
            $permissions = $this->roleWithPermissionsRepository->findOneByIdentifier($role)->permissions();
            $permissionKey = sprintf('action:%s', self::ACL);
            $this->assertFalse($permissions[$permissionKey]);
        }
    }

    /**
     * @return string[]
     */
    private function getRoles(): array
    {
        return $this->connection->fetchFirstColumn(
            'SELECT role FROM oro_access_role',
        );
    }
}
