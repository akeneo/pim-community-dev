<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\TableAttribute\tests\back\Integration;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TableConfiguration;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TextColumn;
use Akeneo\Pim\TableAttribute\Infrastructure\TableConfiguration\Repository\SqlTableConfigurationRepository;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

/**
 * @author Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 */
final class SqlTableConfigurationRepositoryIntegration extends TestCase
{
    private int $tableAttributeId;
    private SqlTableConfigurationRepository $sqlTableConfigurationRepository;
    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sqlTableConfigurationRepository = $this->get(TableConfigurationRepository::class);
        $this->connection = $this->get('database_connection');
        $this->loadFixtures();
    }

    public function testItSavesATableConfiguration(): void
    {
        $tableConfiguration = TableConfiguration::fromColumnDefinitions([
            TextColumn::fromNormalized(['code' => 'ingredients']),
            TextColumn::fromNormalized(['code' => 'quantity']),
        ]);
        $this->sqlTableConfigurationRepository->save($this->tableAttributeId, $tableConfiguration);

        $rows = $this->connection->executeQuery(
            'SELECT * FROM pim_catalog_table_column WHERE attribute_id = :attribute_id ORDER BY column_order',
            ['attribute_id' => $this->tableAttributeId]
        )->fetchAll();

        self::assertCount(2, $rows);
        self::assertEquals(0, $rows[0]['column_order']);
        self::assertSame('ingredients', $rows[0]['code']);
        self::assertSame('text', $rows[0]['data_type']);
        self::assertSame('ingredients_', substr($rows[0]['id'], 0, strlen('ingredients_')));
        self::assertEquals(1, $rows[1]['column_order']);
        self::assertSame('quantity', $rows[1]['code']);
        self::assertSame('text', $rows[1]['data_type']);
        self::assertSame('quantity_', substr($rows[1]['id'], 0, strlen('quantity_')));
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function loadFixtures(): void
    {
        $loadAttributeSql = <<<SQL
INSERT INTO pim_catalog_attribute (sort_order, useable_as_grid_filter, is_required, is_unique, is_localizable, is_scopable, code, entity_type, attribute_type, backend_type, created, updated, guidelines)
VALUES (1, 0, 1, 1, 0, 0, 'nutrition', 'Akeneo\\Pim\\Enrichment\\Component\\Product\\Model\\Product', 'pim_catalog_table', 'table', '2021-05-18 08:43:55', '2021-05-18 08:43:55', '[]');
SQL;
        $this->connection->executeQuery($loadAttributeSql);

        $this->tableAttributeId = (int) $this->connection->lastInsertId();
    }
}
