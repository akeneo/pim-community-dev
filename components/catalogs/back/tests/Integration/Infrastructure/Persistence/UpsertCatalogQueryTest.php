<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence;

use Akeneo\Catalogs\Infrastructure\Persistence\UpsertCatalogQuery;
use Akeneo\Catalogs\ServiceAPI\Model\Catalog;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Persistence\UpsertCatalogQuery
 */
class UpsertCatalogQueryTest extends IntegrationTestCase
{
    private ?UpsertCatalogQuery $query;
    private ?Connection $connection;

    public function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();

        $this->connection = self::getContainer()->get(Connection::class);
        $this->query = self::getContainer()->get(UpsertCatalogQuery::class);
    }

    public function testItCreatesACatalog(): void
    {
        $owner = $this->createUser('test');
        $id = 'db1079b6-f397-4a6a-bae4-8658e64ad47c';

        $this->query->execute($id, 'Store US', 'test', false);

        $this->assertCatalogExists([
            'id' => $id,
            'name' => 'Store US',
            'owner_id' => $owner->getId(),
            'is_enabled' => '0',
        ]);
    }

    public function testItUpdatesACatalog(): void
    {
        $owner = $this->createUser('test');
        $id = 'db1079b6-f397-4a6a-bae4-8658e64ad47c';

        $this->query->execute($id, 'Store US', 'test', false);
        $this->query->execute($id, 'Store US [NEW]', 'test', false);

        $this->assertCatalogExists([
            'id' => $id,
            'name' => 'Store US [NEW]',
            'owner_id' => $owner->getId(),
            'is_enabled' => '0',
        ]);
        $this->assertCountCatalogs(1);
    }

    private function assertCountCatalogs(int $expected): void
    {
        $query = <<<SQL
        SELECT COUNT(*)
        FROM akeneo_catalog catalog
        SQL;

        $result = (int) $this->connection->executeQuery($query)->fetchOne();

        $this->assertEquals($expected, $result);
    }

    private function assertCatalogExists(array $values): void
    {
        $query = <<<SQL
        SELECT
            BIN_TO_UUID(catalog.id) AS id,
            catalog.name,
            catalog.owner_id,
            catalog.is_enabled
        FROM akeneo_catalog catalog
        WHERE id = :id
        SQL;

        $row = $this->connection->executeQuery($query, [
            'id' => Uuid::fromString($values['id'])->getBytes(),
        ])->fetchAssociative();

        $this->assertEquals($values, $row);
    }
}
