<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence;

use Akeneo\Catalogs\Application\Persistence\UpdateCatalogProductValueFiltersQueryInterface;
use Akeneo\Catalogs\Infrastructure\Persistence\UpdateCatalogProductValueFiltersQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Persistence\UpdateCatalogProductValueFiltersQuery
 */
class UpdateCatalogProductValueFiltersQueryTest extends IntegrationTestCase
{
    private ?UpdateCatalogProductValueFiltersQueryInterface $query;
    private ?Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();

        $this->connection = self::getContainer()->get(Connection::class);
        $this->query = self::getContainer()->get(UpdateCatalogProductValueFiltersQuery::class);
    }

    public function testItUpdatesProductValueFilters(): void
    {
        $this->createUser('shopifi');
        $this->createCatalog('db1079b6-f397-4a6a-bae4-8658e64ad47c', 'Store US', 'shopifi');

        $this->query->execute('db1079b6-f397-4a6a-bae4-8658e64ad47c', [
            'channel' => ['ecommerce', 'mobile'],
        ]);

        $this->assertCatalogHasProductValueFilters('db1079b6-f397-4a6a-bae4-8658e64ad47c', [
            'channel' => ['ecommerce', 'mobile'],
        ]);
    }

    private function assertCatalogHasProductValueFilters(string $id, array $expected): void
    {
        $query = <<<SQL
        SELECT catalog.product_value_filters
        FROM akeneo_catalog catalog
        WHERE id = :id
        SQL;

        $row = $this->connection->executeQuery($query, [
            'id' => Uuid::fromString($id)->getBytes(),
        ])->fetchOne();

        $this->assertEquals($expected, \json_decode($row, true, 512, JSON_THROW_ON_ERROR));
    }
}
