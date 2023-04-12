<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence\Catalog;

use Akeneo\Catalogs\Infrastructure\Persistence\Catalog\DeleteCatalogQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Persistence\Catalog\DeleteCatalogQuery
 */
class DeleteCatalogQueryTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();
    }

    public function testItDeletesACatalog(): void
    {
        $id = 'db1079b6-f397-4a6a-bae4-8658e64ad47c';
        $this->createUser('test');
        $this->createCatalog($id, 'Store US', 'test');

        $this->assertCatalogExists($id);

        self::getContainer()->get(DeleteCatalogQuery::class)->execute($id);

        $this->assertCatalogDoesNotExists($id);
    }

    private function assertCatalogExists(string $id): void
    {
        $query = <<<SQL
        SELECT COUNT(*)
        FROM akeneo_catalog catalog
        WHERE id = :id
        SQL;

        $count = (int) self::getContainer()->get(Connection::class)->executeQuery($query, [
            'id' => Uuid::fromString($id)->getBytes(),
        ])->fetchOne();

        $this->assertSame(1, $count);
    }

    private function assertCatalogDoesNotExists(string $id): void
    {
        $query = <<<SQL
        SELECT COUNT(*)
        FROM akeneo_catalog catalog
        WHERE id = :id
        SQL;

        $count = (int) self::getContainer()->get(Connection::class)->executeQuery($query, [
            'id' => Uuid::fromString($id)->getBytes(),
        ])->fetchOne();

        $this->assertSame(0, $count);
    }
}
