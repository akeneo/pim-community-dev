<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Service\User;

use Akeneo\Connectivity\Connection\Infrastructure\Service\User\DeleteUserRole;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\Enrichment\UserRoleLoader;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DeleteUserRoleIntegration extends TestCase
{
    private DeleteUserRole $deleteUserRole;
    private Connection $connection;
    private UserRoleLoader $userRoleLoader;

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->deleteUserRole = $this->get(DeleteUserRole::class);
        $this->connection = $this->get('database_connection');
        $this->userRoleLoader = $this->get('akeneo_connectivity.connection.fixtures.enrichment.user_role_loader');
    }

    /**
     * @group ce
     */
    public function test_it_deletes_an_user_role(): void
    {
        $this->userRoleLoader->create([
            'role' => 'ROLE_FOO',
            'label' => 'Foo',
        ]);

        $this->assertEquals([
            'ROLE_ADMINISTRATOR',
            'ROLE_CATALOG_MANAGER',
            'ROLE_FOO',
            'ROLE_USER',
        ], $this->fetchUserRoles());

        $this->deleteUserRole->execute('ROLE_FOO');

        $this->assertEquals([
            'ROLE_ADMINISTRATOR',
            'ROLE_CATALOG_MANAGER',
            'ROLE_USER',
        ], $this->fetchUserRoles());
    }

    private function fetchUserRoles(): array
    {
        $query = <<<SQL
SELECT role
FROM oro_access_role
ORDER BY role
SQL;

        return $this->connection->fetchFirstColumn($query);
    }
}
