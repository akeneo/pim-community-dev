<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence;

use Akeneo\Catalogs\Infrastructure\Persistence\DeleteCatalogQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DeleteCatalogQueryTest extends IntegrationTestCase
{
    private DeleteCatalogQuery $query;
    private Connection $connection;

    public function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();

        $this->connection = self::getContainer()->get(Connection::class);
        $this->query = self::getContainer()->get(DeleteCatalogQuery::class);
    }

    public function testItDeletesACatalog(): void
    {
        $this->insertCatalog([
            'id' => 'db1079b6-f397-4a6a-bae4-8658e64ad47c',
            'name' => 'Store US',
        ]);

        $this->query->execute('db1079b6-f397-4a6a-bae4-8658e64ad47c');

        $this->assertCatalogDoNotExists('db1079b6-f397-4a6a-bae4-8658e64ad47c');
    }

    private function insertCatalog(array $values): void
    {
        $this->connection->insert(
            'akeneo_catalog',
            \array_merge($values, [
                'id' => Uuid::fromString($values['id'])->getBytes(),
            ])
        );
    }

    private function assertCatalogDoNotExists(string $id): void
    {
        $query = <<<SQL
        SELECT COUNT(*)
        FROM akeneo_catalog catalog
        WHERE id = :id
        SQL;

        $count = (int) $this->connection->executeQuery($query, [
            'id' => Uuid::fromString($id)->getBytes(),
        ])->fetchOne();

        $this->assertSame(0, $count);
    }
}
