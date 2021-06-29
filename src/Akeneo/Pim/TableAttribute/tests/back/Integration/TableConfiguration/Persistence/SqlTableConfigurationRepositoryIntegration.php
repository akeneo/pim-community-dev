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

namespace Akeneo\Pim\TableAttribute\tests\back\Integration\TableConfiguration\Persistence;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\NumberColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationNotFoundException;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TableConfiguration;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TextColumn;
use Akeneo\Pim\TableAttribute\Infrastructure\TableConfiguration\Repository\SqlTableConfigurationRepository;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

final class SqlTableConfigurationRepositoryIntegration extends TestCase
{
    private int $tableAttributeId;
    private SqlTableConfigurationRepository $sqlTableConfigurationRepository;
    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sqlTableConfigurationRepository = $this->get(SqlTableConfigurationRepository::class);
        $this->connection = $this->get('database_connection');
        $this->loadFixtures();
    }

    /** @test */
    public function it_saves_a_new_table_configuration(): void
    {
        $tableConfiguration = TableConfiguration::fromColumnDefinitions([
            TextColumn::fromNormalized(['code' => 'ingredients']),
            TextColumn::fromNormalized(['code' => 'quantity']),
            SelectColumn::fromNormalized(['code' => 'isAllergenic', 'options' => [
                ['code' => 'sugar'],
                ['code' => 'salt', 'labels' => ['en_US' => 'Salt', 'fr_FR' => 'Sel']],
            ]]),
        ]);
        $this->sqlTableConfigurationRepository->save('nutrition', $tableConfiguration);

        $rows = $this->connection->executeQuery(
            'SELECT * FROM pim_catalog_table_column WHERE attribute_id = :attribute_id ORDER BY column_order',
            ['attribute_id' => $this->tableAttributeId]
        )->fetchAll();

        self::assertCount(3, $rows);
        self::assertEquals(0, $rows[0]['column_order']);
        self::assertSame('ingredients', $rows[0]['code']);
        self::assertSame('text', $rows[0]['data_type']);
        self::assertSame('ingredients_', substr($rows[0]['id'], 0, strlen('ingredients_')));
        self::assertEquals(1, $rows[1]['column_order']);
        self::assertSame('quantity', $rows[1]['code']);
        self::assertSame('text', $rows[1]['data_type']);
        self::assertSame('quantity_', substr($rows[1]['id'], 0, strlen('quantity_')));
        self::assertSame('select', $rows[2]['data_type']);
        self::assertSame('isAllergenic', $rows[2]['code']);
        self::assertSame('2', $rows[2]['column_order']);
    }

    /** @test */
    public function it_updates_a_table_configuration(): void
    {
        $tableConfiguration = TableConfiguration::fromColumnDefinitions([
            TextColumn::fromNormalized(['code' => 'ingredients']),
            TextColumn::fromNormalized(['code' => 'quantity']),
        ]);
        $this->sqlTableConfigurationRepository->save('nutrition', $tableConfiguration);

        $rows = $this->connection->executeQuery(
            'SELECT * FROM pim_catalog_table_column WHERE attribute_id = :attribute_id ORDER BY column_order',
            ['attribute_id' => $this->tableAttributeId]
        )->fetchAll();

        self::assertCount(2, $rows);
        $idQuantity = $rows[1]['id'];

        $tableConfiguration = TableConfiguration::fromColumnDefinitions([
            TextColumn::fromNormalized(['code' => 'quantity']),
            TextColumn::fromNormalized(['code' => 'aqr']),
        ]);
        $this->sqlTableConfigurationRepository->save('nutrition', $tableConfiguration);

        $rows = $this->connection->executeQuery(
            'SELECT * FROM pim_catalog_table_column WHERE attribute_id = :attribute_id ORDER BY column_order',
            ['attribute_id' => $this->tableAttributeId]
        )->fetchAll();

        self::assertCount(2, $rows);
        self::assertSame('quantity', $rows[0]['code']);
        self::assertSame($idQuantity, $rows[0]['id']);
        self::assertSame('aqr', $rows[1]['code']);
    }

    /** @test */
    public function it_updates_a_table_configuration_and_changes_the_columns_order(): void
    {
        $tableConfiguration = TableConfiguration::fromColumnDefinitions([
            TextColumn::fromNormalized(['code' => 'ingredients']),
            TextColumn::fromNormalized(['code' => 'quantity']),
            TextColumn::fromNormalized(['code' => 'price']),
        ]);
        $this->sqlTableConfigurationRepository->save('nutrition', $tableConfiguration);

        $rows = $this->connection->executeQuery(
            'SELECT * FROM pim_catalog_table_column WHERE attribute_id = :attribute_id ORDER BY column_order',
            ['attribute_id' => $this->tableAttributeId]
        )->fetchAll();

        self::assertCount(3, $rows);
        $idIngredients = $rows[0]['id'];
        $idQuantity = $rows[1]['id'];
        $idPrice = $rows[2]['id'];

        $tableConfiguration = TableConfiguration::fromColumnDefinitions([
            TextColumn::fromNormalized(['code' => 'ingredients']),
            TextColumn::fromNormalized(['code' => 'price']),
            TextColumn::fromNormalized(['code' => 'quantity']),
        ]);
        $this->sqlTableConfigurationRepository->save('nutrition', $tableConfiguration);

        $rows = $this->connection->executeQuery(
            'SELECT * FROM pim_catalog_table_column WHERE attribute_id = :attribute_id ORDER BY column_order',
            ['attribute_id' => $this->tableAttributeId]
        )->fetchAll();

        self::assertCount(3, $rows);
        self::assertSame('ingredients', $rows[0]['code']);
        self::assertSame($idIngredients, $rows[0]['id']);
        self::assertSame('price', $rows[1]['code']);
        self::assertSame($idPrice, $rows[1]['id']);
        self::assertSame('quantity', $rows[2]['code']);
        self::assertSame($idQuantity, $rows[2]['id']);
    }

    /** @test */
    public function it_recreates_the_column_when_data_type_is_updated(): void
    {
        $tableConfiguration = TableConfiguration::fromColumnDefinitions([
            TextColumn::fromNormalized(['code' => 'ingredients']),
            TextColumn::fromNormalized(['code' => 'quantity']),
        ]);
        $this->sqlTableConfigurationRepository->save('nutrition', $tableConfiguration);

        $rows = $this->connection->executeQuery(
            'SELECT * FROM pim_catalog_table_column WHERE attribute_id = :attribute_id ORDER BY column_order',
            ['attribute_id' => $this->tableAttributeId]
        )->fetchAll();

        self::assertCount(2, $rows);
        $idIngredients = $rows[0]['id'];
        $idQuantity = $rows[1]['id'];
        self::assertSame('text', $rows[1]['data_type']);

        $tableConfiguration = TableConfiguration::fromColumnDefinitions([
            TextColumn::fromNormalized(['code' => 'ingredients']),
            NumberColumn::fromNormalized(['code' => 'quantity']),
        ]);
        $this->sqlTableConfigurationRepository->save('nutrition', $tableConfiguration);

        $rows = $this->connection->executeQuery(
            'SELECT * FROM pim_catalog_table_column WHERE attribute_id = :attribute_id ORDER BY column_order',
            ['attribute_id' => $this->tableAttributeId]
        )->fetchAll();

        self::assertCount(2, $rows);
        self::assertSame('ingredients', $rows[0]['code']);
        self::assertSame($idIngredients, $rows[0]['id']);
        self::assertSame('quantity', $rows[1]['code']);
        self::assertSame('number', $rows[1]['data_type'], 'The column data type should have changed');
        self::assertNotSame($idQuantity, $rows[1]['id'], 'The column id should have changed when the type is updated');
    }

    /** @test */
    public function it_fetches_a_table_configuration_by_attribute_code(): void
    {
        $sql = <<<SQL
        INSERT INTO pim_catalog_table_column (id, attribute_id, code, data_type, column_order, labels, validations)
        VALUES (:id, :attribute_id, :code, :data_type, :column_order, :labels, :validations)
        SQL;
        $this->connection->executeQuery(
            $sql,
            [
                'id' => 'ingredients_1234567890',
                'attribute_id' => $this->tableAttributeId,
                'code' => 'ingredients',
                'data_type' => 'select',
                'column_order' => 0,
                'labels' => \json_encode(['en_US' => 'Ingredients', 'fr_FR' => 'Ingrédients']),
                'validations' => '{}',
            ]
        );
        $this->connection->executeQuery(
            $sql,
            [
                'id' => 'quantity_2345678901',
                'attribute_id' => $this->tableAttributeId,
                'code' => 'quantity',
                'data_type' => 'text',
                'column_order' => 1,
                'labels' => '{}',
                'validations' => \json_encode(['max_length' => 90]),
            ]
        );

        $sql = <<<SQL
        INSERT INTO pim_catalog_table_column_select_option (column_id, code, labels)
        VALUES (:column_id, :code, :labels)
        SQL;

        $this->connection->executeQuery($sql, [
            'column_id' => 'ingredients_1234567890',
            'code' => 'sugar',
            'labels' => '{}',
        ]);

        $this->connection->executeQuery($sql, [
            'column_id' => 'ingredients_1234567890',
            'code' => 'salt',
            'labels' => '{}',
        ]);

        $result = $this->sqlTableConfigurationRepository->getByAttributeCode('nutrition');

        self::assertEqualsCanonicalizing(
            [
                [
                    'code' => 'ingredients',
                    'data_type' => 'select',
                    'labels' => ['en_US' => 'Ingredients', 'fr_FR' => 'Ingrédients'],
                    'validations' => (object)[],
                ],
                [
                    'code' => 'quantity',
                    'data_type' => 'text',
                    'labels' => (object)[],
                    'validations' => ['max_length' => 90],
                ]
            ],
            $result->normalize()
        );
    }

    /** @test */
    public function it_throws_an_exception_if_table_configuration_cannot_be_fetched(): void
    {
        $this->expectException(TableConfigurationNotFoundException::class);
        $this->expectExceptionMessage('Could not find table configuration for the "yolo" attribute');

        $this->sqlTableConfigurationRepository->getByAttributeCode('yolo');
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
