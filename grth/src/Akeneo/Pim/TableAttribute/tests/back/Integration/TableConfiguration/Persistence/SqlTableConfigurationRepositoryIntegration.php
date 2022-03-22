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

namespace Akeneo\Test\Pim\TableAttribute\Integration\TableConfiguration\Persistence;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\BooleanColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\MeasurementColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\NumberColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ReferenceEntityColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationNotFoundException;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TableConfiguration;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TextColumn;
use Akeneo\Pim\TableAttribute\Infrastructure\TableConfiguration\Repository\SqlTableConfigurationRepository;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\Pim\TableAttribute\Helper\ColumnIdGenerator;
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
            SelectColumn::fromNormalized([
                'id' => ColumnIdGenerator::ingredient(),
                'code' => 'ingredient',
                'options' => [
                    ['code' => 'sugar'],
                    ['code' => 'salt', 'labels' => ['en_US' => 'Salt', 'fr_FR' => 'Sel']],
                ],
                'is_required_for_completeness' => true,
            ]),
            TextColumn::fromNormalized(['id' => ColumnIdGenerator::quantity(), 'code' => 'quantity']),
            TextColumn::fromNormalized(['id' => ColumnIdGenerator::isAllergenic(), 'code' => 'is_allergenic']),
            ReferenceEntityColumn::fromNormalized(['id' => ColumnIdGenerator::record(), 'code' => 'record', 'reference_entity_identifier' => 'entity']),
            MeasurementColumn::fromNormalized([
                'id' => ColumnIdGenerator::duration(),
                'code' => 'duration',
                'measurement_family_code' => 'family',
                'measurement_default_unit_code' => 'unit',
            ]),
        ]);
        $this->sqlTableConfigurationRepository->save('nutrition', $tableConfiguration);

        $rows = $this->connection->executeQuery(
            'SELECT * FROM pim_catalog_table_column WHERE attribute_id = :attribute_id ORDER BY column_order',
            ['attribute_id' => $this->tableAttributeId]
        )->fetchAllAssociative();

        self::assertCount(5, $rows);
        self::assertSame(0, (int)$rows[0]['column_order']);
        self::assertSame('ingredient', $rows[0]['code']);
        self::assertSame('select', $rows[0]['data_type']);
        self::assertSame('1', $rows[0]['is_required_for_completeness']);
        self::assertSame(ColumnIdGenerator::ingredient(), $rows[0]['id']);
        self::assertSame(1, (int)$rows[1]['column_order']);
        self::assertSame('quantity', $rows[1]['code']);
        self::assertSame('text', $rows[1]['data_type']);
        self::assertSame('0', $rows[1]['is_required_for_completeness']);
        self::assertSame(ColumnIdGenerator::quantity(), $rows[1]['id']);
        self::assertSame('text', $rows[2]['data_type']);
        self::assertSame('is_allergenic', $rows[2]['code']);
        self::assertSame(ColumnIdGenerator::isAllergenic(), $rows[2]['id']);
        self::assertSame(2, (int)$rows[2]['column_order']);
        self::assertSame('0', $rows[2]['is_required_for_completeness']);
        self::assertSame(ColumnIdGenerator::record(), $rows[3]['id']);
        self::assertSame(3, (int)$rows[3]['column_order']);
        self::assertSame('0', $rows[3]['is_required_for_completeness']);
        self::assertSame('{"reference_entity_identifier": "entity"}', $rows[3]['properties']);

        self::assertSame(ColumnIdGenerator::duration(), $rows[4]['id']);
        self::assertSame(4, (int)$rows[4]['column_order']);
        self::assertSame('0', $rows[4]['is_required_for_completeness']);
        $properties = \json_decode($rows[4]['properties'], true, 512, JSON_THROW_ON_ERROR);
        self::assertCount(2, $properties);
        self::assertSame('family', $properties['measurement_family_code'] ?? null);
        self::assertSame('unit', $properties['measurement_default_unit_code'] ?? null);
    }

    /** @test */
    public function it_updates_a_table_configuration(): void
    {
        $tableConfiguration = TableConfiguration::fromColumnDefinitions([
            SelectColumn::fromNormalized(['id' => ColumnIdGenerator::ingredient(), 'code' => 'ingredient', 'is_required_for_completeness' => true]),
            TextColumn::fromNormalized(['id' => ColumnIdGenerator::quantity(), 'code' => 'quantity']),
            BooleanColumn::fromNormalized(['id' => ColumnIdGenerator::isAllergenic(), 'code' => 'is_allergenic']),
            ReferenceEntityColumn::fromNormalized(['id' => ColumnIdGenerator::record(), 'code' => 'record', 'reference_entity_identifier' => 'entity']),
            MeasurementColumn::fromNormalized([
                'id' => ColumnIdGenerator::duration(),
                'code' => 'duration',
                'measurement_family_code' => 'family',
                'measurement_default_unit_code' => 'unit',
            ]),
        ]);
        $this->sqlTableConfigurationRepository->save('nutrition', $tableConfiguration);

        $ids = $this->connection->executeQuery(
            'SELECT id FROM pim_catalog_table_column WHERE attribute_id = :attribute_id ORDER BY column_order',
            ['attribute_id' => $this->tableAttributeId]
        )->fetchFirstColumn();

        self::assertCount(5, $ids);
        [$ingredientId, $quantityId,] = $ids;

        $tableConfiguration = TableConfiguration::fromColumnDefinitions([
            SelectColumn::fromNormalized(['id' => $ingredientId, 'code' => 'ingredient', 'is_required_for_completeness' => true]),
            TextColumn::fromNormalized(['id' => ColumnIdGenerator::generateAsString('aqr'), 'code' => 'aqr']),
            TextColumn::fromNormalized(['id' => $quantityId, 'code' => 'quantity']),
        ]);
        $this->sqlTableConfigurationRepository->save('nutrition', $tableConfiguration);

        $rows = $this->connection->executeQuery(
            'SELECT * FROM pim_catalog_table_column WHERE attribute_id = :attribute_id ORDER BY column_order',
            ['attribute_id' => $this->tableAttributeId]
        )->fetchAllAssociative();

        self::assertCount(3, $rows);
        self::assertSame('ingredient', $rows[0]['code']);
        self::assertSame($ingredientId, $rows[0]['id']);
        self::assertSame('aqr', $rows[1]['code']);
        self::assertSame('quantity', $rows[2]['code']);
        self::assertSame($quantityId, $rows[2]['id']);
    }

    /** @test */
    public function it_updates_a_table_configuration_with_case_insensitive(): void
    {
        $tableConfiguration = TableConfiguration::fromColumnDefinitions([
            SelectColumn::fromNormalized(['id' => ColumnIdGenerator::ingredient(), 'code' => 'ingredient', 'is_required_for_completeness' => true]),
            TextColumn::fromNormalized(['id' => ColumnIdGenerator::quantity(), 'code' => 'quantity']),
            BooleanColumn::fromNormalized(['id' => ColumnIdGenerator::isAllergenic(), 'code' => 'isAllergenic']),
        ]);
        $this->sqlTableConfigurationRepository->save('nutrition', $tableConfiguration);

        $ids = $this->connection->executeQuery(
            'SELECT id FROM pim_catalog_table_column WHERE attribute_id = :attribute_id ORDER BY column_order',
            ['attribute_id' => $this->tableAttributeId]
        )->fetchFirstColumn();

        self::assertCount(3, $ids);
        self::assertContains(ColumnIdGenerator::ingredient(), $ids);
        self::assertContains(ColumnIdGenerator::quantity(), $ids);
        self::assertContains(ColumnIdGenerator::isAllergenic(), $ids);

        $tableConfiguration = TableConfiguration::fromColumnDefinitions([
            SelectColumn::fromNormalized(['id' => ColumnIdGenerator::ingredient(), 'code' => 'INGredients', 'is_required_for_completeness' => true]),
            TextColumn::fromNormalized(['id' => ColumnIdGenerator::generateAsString('aqr'), 'code' => 'aqr']),
            TextColumn::fromNormalized(['id' => ColumnIdGenerator::quantity(), 'code' => 'QUANTITY']),
        ]);
        $this->sqlTableConfigurationRepository->save('NUTrition', $tableConfiguration);

        $rows = $this->connection->executeQuery(
            'SELECT * FROM pim_catalog_table_column WHERE attribute_id = :attribute_id ORDER BY column_order',
            ['attribute_id' => $this->tableAttributeId]
        )->fetchAllAssociative();

        self::assertCount(3, $rows);
        self::assertSame('ingredient', $rows[0]['code']);
        // It's really important the column is not recreated (= the id still the same).
        // Otherwise all its options are deleted by cascade.
        self::assertSame(ColumnIdGenerator::ingredient(), $rows[0]['id']);
        self::assertSame('quantity', $rows[2]['code']);
        self::assertSame(ColumnIdGenerator::quantity(), $rows[2]['id']);
        self::assertSame('aqr', $rows[1]['code']);
    }

    /** @test */
    public function it_saves_a_table_configuration_with_reserved_keywords_as_column_codes()
    {
        $tableConfiguration = TableConfiguration::fromColumnDefinitions(
            [
                SelectColumn::fromNormalized(['id' => ColumnIdGenerator::generateAsString('select'), 'code' => 'select', 'is_required_for_completeness' => true]),
                TextColumn::fromNormalized(['id' => ColumnIdGenerator::generateAsString('text'), 'code' => 'text']),
                BooleanColumn::fromNormalized(['id' => ColumnIdGenerator::generateAsString('boolean'), 'code' => 'boolean']),
                NumberColumn::fromNormalized(['id' => ColumnIdGenerator::generateAsString('number'), 'code' => 'number']),
            ]
        );
        $this->sqlTableConfigurationRepository->save('nutrition', $tableConfiguration);

        $rows = $this->connection->executeQuery(
            'SELECT * FROM pim_catalog_table_column WHERE attribute_id = :attribute_id ORDER BY column_order',
            ['attribute_id' => $this->tableAttributeId]
        )->fetchAllAssociative();

        self::assertCount(4, $rows);
    }

    /** @test */
    public function it_updates_a_table_configuration_and_changes_the_columns_order(): void
    {
        $priceId = ColumnIdGenerator::generateAsString('price');
        $tableConfiguration = TableConfiguration::fromColumnDefinitions([
            SelectColumn::fromNormalized(['id' => ColumnIdGenerator::ingredient(), 'code' => 'ingredient', 'is_required_for_completeness' => true]),
            TextColumn::fromNormalized(['id' => ColumnIdGenerator::quantity(), 'code' => 'quantity']),
            TextColumn::fromNormalized(['id' => $priceId, 'code' => 'price']),
        ]);
        $this->sqlTableConfigurationRepository->save('nutrition', $tableConfiguration);

        $tableConfiguration = TableConfiguration::fromColumnDefinitions([
            SelectColumn::fromNormalized(['id' => ColumnIdGenerator::ingredient(), 'code' => 'ingredient', 'is_required_for_completeness' => true]),
            TextColumn::fromNormalized(['id' => $priceId, 'code' => 'price']),
            TextColumn::fromNormalized(['id' => ColumnIdGenerator::quantity(), 'code' => 'quantity']),
        ]);
        $this->sqlTableConfigurationRepository->save('nutrition', $tableConfiguration);

        $rows = $this->connection->executeQuery(
            'SELECT * FROM pim_catalog_table_column WHERE attribute_id = :attribute_id ORDER BY column_order',
            ['attribute_id' => $this->tableAttributeId]
        )->fetchAllAssociative();

        self::assertCount(3, $rows);
        self::assertSame('ingredient', $rows[0]['code']);
        self::assertSame(ColumnIdGenerator::ingredient(), $rows[0]['id']);
        self::assertSame('price', $rows[1]['code']);
        self::assertSame($priceId, $rows[1]['id']);
        self::assertSame('quantity', $rows[2]['code']);
        self::assertSame(ColumnIdGenerator::quantity(), $rows[2]['id']);
    }

    /** @test */
    public function it_fetches_a_table_configuration_by_attribute_code(): void
    {
        $sql = <<<SQL
        INSERT INTO pim_catalog_table_column (id, attribute_id, code, data_type, column_order, labels, validations, is_required_for_completeness, properties)
        VALUES (:id, :attribute_id, :code, :data_type, :column_order, :labels, :validations, :is_required_for_completeness, :properties)
        SQL;
        $this->connection->executeQuery(
            $sql,
            [
                'id' => ColumnIdGenerator::ingredient(),
                'attribute_id' => $this->tableAttributeId,
                'code' => 'ingredient',
                'data_type' => 'select',
                'column_order' => 0,
                'labels' => \json_encode(['en_US' => 'Ingredient', 'af_ZA' => 'Ingrédient']),
                'validations' => '{}',
                'is_required_for_completeness' => '1',
                'properties' => '{}',
            ]
        );
        $this->connection->executeQuery(
            $sql,
            [
                'id' => ColumnIdGenerator::quantity(),
                'attribute_id' => $this->tableAttributeId,
                'code' => 'quantity',
                'data_type' => 'text',
                'column_order' => 1,
                'labels' => '{}',
                'validations' => \json_encode(['max_length' => 90]),
                'is_required_for_completeness' => '0',
                'properties' => '{}',
            ]
        );
        $this->connection->executeQuery(
            $sql,
            [
                'id' => ColumnIdGenerator::record(),
                'attribute_id' => $this->tableAttributeId,
                'code' => 'record',
                'data_type' => 'reference_entity',
                'column_order' => 2,
                'labels' => '{}',
                'validations' => '{}',
                'is_required_for_completeness' => '0',
                'properties' => \json_encode(['reference_entity_identifier' => 'entity']),
            ]
        );
        $this->connection->executeQuery(
            $sql,
            [
                'id' => ColumnIdGenerator::duration(),
                'attribute_id' => $this->tableAttributeId,
                'code' => 'duration',
                'data_type' => 'measurement',
                'column_order' => 3,
                'labels' => '{}',
                'validations' => '{}',
                'is_required_for_completeness' => '0',
                'properties' => \json_encode(['measurement_family_code' => 'family', 'measurement_default_unit_code' => 'unit']),
            ]
        );

        $sql = <<<SQL
        INSERT INTO pim_catalog_table_column_select_option (column_id, code, labels)
        VALUES (:column_id, :code, :labels)
        SQL;

        $this->connection->executeQuery($sql, [
            'column_id' => ColumnIdGenerator::ingredient(),
            'code' => 'sugar',
            'labels' => '{}',
        ]);

        $this->connection->executeQuery($sql, [
            'column_id' => ColumnIdGenerator::ingredient(),
            'code' => 'salt',
            'labels' => '{}',
        ]);

        $result = $this->sqlTableConfigurationRepository->getByAttributeCode('nutrition');

        self::assertEqualsCanonicalizing(
            [
                [
                    'id' => ColumnIdGenerator::ingredient(),
                    'code' => 'ingredient',
                    'data_type' => 'select',
                    'labels' => ['en_US' => 'Ingredient'],
                    'validations' => (object)[],
                    'is_required_for_completeness' => true,
                ],
                [
                    'id' => ColumnIdGenerator::quantity(),
                    'code' => 'quantity',
                    'data_type' => 'text',
                    'labels' => (object)[],
                    'validations' => ['max_length' => 90],
                    'is_required_for_completeness' => false,
                ],
                [
                    'id' => ColumnIdGenerator::record(),
                    'code' => 'record',
                    'data_type' => 'reference_entity',
                    'labels' => (object)[],
                    'validations' => (object) [],
                    'is_required_for_completeness' => false,
                    'reference_entity_identifier' => 'entity',
                ],
                [
                    'id' => ColumnIdGenerator::duration(),
                    'code' => 'duration',
                    'data_type' => 'measurement',
                    'labels' => (object)[],
                    'validations' => (object) [],
                    'is_required_for_completeness' => false,
                    'measurement_family_code' => 'family',
                    'measurement_default_unit_code' => 'unit',
                ],
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

        $this->tableAttributeId = (int)$this->connection->lastInsertId();
    }
}
