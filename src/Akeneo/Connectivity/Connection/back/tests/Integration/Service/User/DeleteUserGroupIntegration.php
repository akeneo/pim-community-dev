<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Service\User;

use Akeneo\Connectivity\Connection\Infrastructure\Service\User\DeleteUserGroup;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\Enrichment\UserGroupLoader;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DeleteUserGroupIntegration extends TestCase
{
    private DeleteUserGroup $deleteUserGroup;
    private Connection $connection;
    private UserGroupLoader $userGroupLoader;

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->deleteUserGroup = $this->get(DeleteUserGroup::class);
        $this->connection = $this->get('database_connection');
        $this->userGroupLoader = $this->get('akeneo_connectivity.connection.fixtures.enrichment.user_group_loader');
    }

    public function test_it_deletes_an_user_group(): void
    {
        $this->userGroupLoader->create([
            'name' => 'Foo',
        ]);

        $this->assertEquals([
            'All',
            'Foo',
            'IT support',
            'Manager',
            'Redactor',
        ], $this->fetchUserGroups());

        $this->deleteUserGroup->execute('Foo');

        $this->assertEquals([
            'All',
            'IT support',
            'Manager',
            'Redactor',
        ], $this->fetchUserGroups());
    }

    private function fetchUserGroups(): array
    {
        $query = <<<SQL
SELECT name
FROM oro_access_group
ORDER BY name
SQL;

        return $this->connection->fetchFirstColumn($query);
    }
}
