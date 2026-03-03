<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\CustomApps\Persistence;

use Akeneo\Connectivity\Connection\Infrastructure\CustomApps\Persistence\DeleteCustomAppQuery;
use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\CustomAppLoader;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Connectivity\Connection\Infrastructure\CustomApps\Persistence\DeleteCustomAppQuery
 */
class DeleteCustomAppQueryIntegration extends TestCase
{
    private ?CustomAppLoader $customAppLoader;
    private ?DeleteCustomAppQuery $deleteCustomAppQuery;
    private ?Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customAppLoader = $this->get(CustomAppLoader::class);
        $this->deleteCustomAppQuery = $this->get(DeleteCustomAppQuery::class);
        $this->connection = $this->get('database_connection');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_deletes_a_custom_app(): void
    {
        $this->customAppLoader->create('100eedac-ff5c-497b-899d-e2d64b6c59f9', $this->createAdminUser()->getId());

        $this->deleteCustomAppQuery->execute('100eedac-ff5c-497b-899d-e2d64b6c59f9');

        $this->assertFalse($this->customAppExists('100eedac-ff5c-497b-899d-e2d64b6c59f9'));
    }

    public function test_it_does_nothing_on_unknown_id(): void
    {
        $this->customAppLoader->create('100eedac-ff5c-497b-899d-e2d64b6c59f9', $this->createAdminUser()->getId());

        $this->deleteCustomAppQuery->execute('wrong_id');

        $this->assertTrue($this->customAppExists('100eedac-ff5c-497b-899d-e2d64b6c59f9'));
    }

    private function customAppExists(string $clientId): bool
    {
        $sql = <<<SQL
        SELECT 1
        FROM akeneo_connectivity_test_app
        WHERE client_id = :clientId
        SQL;

        $result = $this->connection->fetchOne($sql, ['clientId' => $clientId]);

        return (bool) $result;
    }
}
