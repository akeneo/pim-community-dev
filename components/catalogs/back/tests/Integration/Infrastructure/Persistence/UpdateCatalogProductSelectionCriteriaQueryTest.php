<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence;

use Akeneo\Catalogs\Infrastructure\Persistence\UpdateCatalogProductSelectionCriteriaQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Persistence\UpdateCatalogProductSelectionCriteriaQuery
 */
class UpdateCatalogProductSelectionCriteriaQueryTest extends IntegrationTestCase
{
    private ?UpdateCatalogProductSelectionCriteriaQuery $query;
    private ?Connection $connection;

    public function setUp(): void
    {
        parent::setUp();

        $this->purgeDataAndLoadMinimalCatalog();

        $this->connection = self::getContainer()->get(Connection::class);
        $this->query = self::getContainer()->get(UpdateCatalogProductSelectionCriteriaQuery::class);
    }

    public function testItUpdatesProductSelectionCriteria(): void
    {
        $owner = $this->createUser('shopifi');
        $this->createCatalog('db1079b6-f397-4a6a-bae4-8658e64ad47c', 'Store US', 'shopifi');

        $this->query->execute('db1079b6-f397-4a6a-bae4-8658e64ad47c', [
            [
                'field' => 'enabled',
                'operator' => '=',
                'value' => true,
            ],
            [
                'field' => 'enabled',
                'operator' => '=',
                'value' => false,
            ],
        ]);

        $this->assertCatalogHasProductSelectionCriteria('db1079b6-f397-4a6a-bae4-8658e64ad47c', [
            [
                'field' => 'enabled',
                'operator' => '=',
                'value' => true,
            ],
            [
                'field' => 'enabled',
                'operator' => '=',
                'value' => false,
            ],
        ]);
    }

    private function assertCatalogHasProductSelectionCriteria(string $id, array $expected): void
    {
        $query = <<<SQL
        SELECT catalog.product_selection_criteria
        FROM akeneo_catalog catalog
        WHERE id = :id
        SQL;

        $row = $this->connection->executeQuery($query, [
            'id' => Uuid::fromString($id)->getBytes(),
        ])->fetchOne();

        $this->assertEquals($expected, \json_decode($row, true));
    }
}
