<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Test\Integration\Infrastructure\Persistence\Catalog;

use Akeneo\Catalogs\Domain\Catalog;
use Akeneo\Catalogs\Infrastructure\Persistence\Catalog\UpsertCatalogQuery;
use Akeneo\Catalogs\Test\Integration\IntegrationTestCase;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @covers \Akeneo\Catalogs\Infrastructure\Persistence\Catalog\UpsertCatalogQuery
 */
class UpsertCatalogQueryTest extends IntegrationTestCase
{
    private ?UpsertCatalogQuery $query;
    private ?Connection $connection;

    protected function setUp(): void
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
        $productSelectionCriteria = [
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
        ];
        $productValueFilters = ['channels' => ['ecommerce', 'mobile']];
        $productMapping = [
            'name' => [
                'source' => 'title',
                'scope' => 'ecommerce',
                'locale' => 'en_US',
            ],
        ];

        $this->query->execute(new Catalog(
            $id,
            'Store US',
            'test',
            false,
            $productSelectionCriteria,
            $productValueFilters,
            $productMapping,
        ));

        $this->assertCatalogExists([
            'id' => $id,
            'name' => 'Store US',
            'owner_id' => $owner->getId(),
            'is_enabled' => '0',
            'product_selection_criteria' => $productSelectionCriteria,
            'product_value_filters' => $productValueFilters,
            'product_mapping' => $productMapping,
        ]);
    }

    public function testItUpdatesACatalog(): void
    {
        $owner = $this->createUser('test');
        $id = 'db1079b6-f397-4a6a-bae4-8658e64ad47c';
        $enabledCriterion = [
            'field' => 'enabled',
            'operator' => '=',
            'value' => true,
        ];
        $disabledCriterion = [
            'field' => 'enabled',
            'operator' => '=',
            'value' => false,
        ];
        $productValueFiltersChannel = ['channels' => ['ecommerce', 'mobile']];
        $productValueFiltersLocale = ['locales' => ['en_US', 'fr_FR']];
        $productMapping = [
            'name' => [
                'source' => 'title',
                'scope' => 'ecommerce',
                'locale' => 'en_US',
            ],
        ];

        $this->query->execute(new Catalog(
            $id,
            'Store US',
            'test',
            false,
            [$enabledCriterion],
            $productValueFiltersChannel,
            [],
        ));

        $this->query->execute(new Catalog(
            $id,
            'Store US [NEW]',
            'test',
            true,
            [$enabledCriterion, $disabledCriterion],
            $productValueFiltersLocale,
            $productMapping,
        ));

        $this->assertCatalogExists([
            'id' => $id,
            'name' => 'Store US [NEW]',
            'owner_id' => $owner->getId(),
            'is_enabled' => '1',
            'product_selection_criteria' => [$enabledCriterion, $disabledCriterion],
            'product_value_filters' => $productValueFiltersLocale,
            'product_mapping' => $productMapping,
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
            catalog.is_enabled,
            catalog.product_selection_criteria,
            catalog.product_value_filters,
            catalog.product_mapping
        FROM akeneo_catalog catalog
        WHERE id = :id
        SQL;

        $row = $this->connection->executeQuery($query, [
            'id' => Uuid::fromString($values['id'])->getBytes(),
        ])->fetchAssociative();

        $row['product_selection_criteria'] = \json_decode($row['product_selection_criteria'], true, 512, JSON_THROW_ON_ERROR);
        $row['product_value_filters'] = \json_decode($row['product_value_filters'], true, 512, JSON_THROW_ON_ERROR);
        $row['product_mapping'] = \json_decode($row['product_mapping'], true, 512, JSON_THROW_ON_ERROR);

        $this->assertEquals($values, $row);
    }

    public function testProductSelectionCriteriaIsNumericallyIndexedOnceCatalogCreated(): void
    {
        $owner = $this->createUser('test');
        $id = 'db1079b6-f397-4a6a-bae4-8658e64ad47c';
        $enabledCriterion = [
            'field' => 'enabled',
            'operator' => '=',
            'value' => true,
        ];
        $disabledCriterion = [
            'field' => 'enabled',
            'operator' => '=',
            'value' => false,
        ];

        $this->query->execute(new Catalog(
            $id,
            'Store US',
            'test',
            false,
            [3 => $enabledCriterion, 2 => $disabledCriterion],
            [],
            [],
        ));

        $this->assertCatalogExists([
            'id' => $id,
            'name' => 'Store US',
            'owner_id' => $owner->getId(),
            'is_enabled' => '0',
            'product_selection_criteria' => [0 => $enabledCriterion, 1 => $disabledCriterion],
            'product_value_filters' => [],
            'product_mapping' => [],
        ]);
    }
}
