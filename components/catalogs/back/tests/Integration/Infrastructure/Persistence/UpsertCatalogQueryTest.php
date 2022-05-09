<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence;

use Akeneo\Catalogs\Domain\Model\Catalog;
use Akeneo\Catalogs\Infrastructure\Persistence\UpsertCatalogQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpsertCatalogQueryTest extends IntegrationTestCase
{
    private UpsertCatalogQuery $query;
    private Connection $connection;

    public function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();

        $this->connection = self::getContainer()->get(Connection::class);
        $this->query = self::getContainer()->get(UpsertCatalogQuery::class);
    }

    public function testItCreatesACatalog(): void
    {
        $id = 'db1079b6-f397-4a6a-bae4-8658e64ad47c';

        $this->query->execute(new Catalog($id, 'Store US'));

        $this->assertCatalogExists([
            'id' => $id,
            'name' => 'Store US',
        ]);
    }

    public function testItUpdatesACatalog(): void
    {
        $id = 'db1079b6-f397-4a6a-bae4-8658e64ad47c';

        $this->query->execute(new Catalog($id, 'Store US'));
        $this->query->execute(new Catalog($id, 'Store US [NEW]'));

        $this->assertCatalogExists([
            'id' => $id,
            'name' => 'Store US [NEW]',
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
            catalog.name
        FROM akeneo_catalog catalog
        WHERE id = :id
        SQL;

        $row = $this->connection->executeQuery($query, [
            'id' => Uuid::fromString($values['id'])->getBytes(),
        ])->fetchAssociative();

        $this->assertEquals($values, $row);
    }
}
