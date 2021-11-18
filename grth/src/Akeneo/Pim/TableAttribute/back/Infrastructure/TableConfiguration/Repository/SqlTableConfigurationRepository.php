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

namespace Akeneo\Pim\TableAttribute\Infrastructure\TableConfiguration\Repository;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Factory\TableConfigurationFactory;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationNotFoundException;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TableConfiguration;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnId;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use Ramsey\Uuid\Uuid;

/**
 * @author Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 */
final class SqlTableConfigurationRepository implements TableConfigurationRepository
{
    private Connection $connection;
    private TableConfigurationFactory $tableConfigurationFactory;

    public function __construct(Connection $connection, TableConfigurationFactory $tableConfigurationFactory)
    {
        $this->connection = $connection;
        $this->tableConfigurationFactory = $tableConfigurationFactory;
    }

    public function getNextIdentifier(ColumnCode $columnCode): ColumnId
    {
        return ColumnId::createFromColumnCode($columnCode, Uuid::uuid4()->toString());
    }

    public function save(string $attributeCode, TableConfiguration $tableConfiguration): void
    {
        if ($this->connection->isTransactionActive()) {
            $this->doSave($attributeCode, $tableConfiguration);

            return;
        }

        $this->connection->transactional(fn () => $this->doSave($attributeCode, $tableConfiguration));
    }

    private function doSave(string $attributeCode, TableConfiguration $tableConfiguration): void
    {
        $columnIds = \array_map(
            fn (array $normalizedColumn): string => $normalizedColumn['id'],
            $tableConfiguration->normalize(),
        );

        $this->connection->executeQuery(
            <<<SQL
            DELETE table_column.* FROM pim_catalog_table_column table_column
            INNER JOIN pim_catalog_attribute attribute ON attribute.id = table_column.attribute_id
            WHERE attribute.code = :attribute_code
                AND table_column.id NOT IN (:column_ids)
            SQL,
            [
                'attribute_code' => $attributeCode,
                'column_ids' => $columnIds,
            ],
            [
                'column_ids' => Connection::PARAM_STR_ARRAY,
            ]
        );

        foreach ($tableConfiguration->normalize() as $columnOrder => $columnDefinition) {
            $this->connection->executeQuery(
                <<<SQL
                INSERT INTO pim_catalog_table_column (id, attribute_id, code, data_type, column_order, labels, validations, is_required_for_completeness)
                SELECT * FROM (
                    SELECT :column_id, attribute.id as attribute_id, :code as column_code, :data_type as data_type, :column_order AS column_order, :labels AS labels, :validations as validations, :is_required_for_completeness as is_required_for_completeness
                    FROM pim_catalog_attribute AS attribute WHERE code = :attribute_code
                ) AS newvalues
                ON DUPLICATE KEY UPDATE column_order = newvalues.column_order, labels = newvalues.labels, validations = newvalues.validations, is_required_for_completeness = newvalues.is_required_for_completeness
                SQL,
                [
                    'column_id' => $columnDefinition['id'],
                    'attribute_code' => $attributeCode,
                    'code' => $columnDefinition['code'],
                    'data_type' => $columnDefinition['data_type'],
                    'column_order' => $columnOrder,
                    'labels' => \json_encode($columnDefinition['labels']),
                    'validations' => \json_encode($columnDefinition['validations']),
                    'is_required_for_completeness' => $columnDefinition['is_required_for_completeness'] ? 1 : 0,
                ]
            );
        }
    }

    public function getByAttributeCode(string $attributeCode): TableConfiguration
    {
        $statement = $this->connection->executeQuery(
            <<<SQL
            SELECT
                table_column.id,
                table_column.code,
                data_type,
                column_order,
                table_column.labels,
                validations,
                is_required_for_completeness
            FROM pim_catalog_table_column table_column
                INNER JOIN pim_catalog_attribute attribute ON attribute.id = table_column.attribute_id
            WHERE attribute.code = :attributeCode
            ORDER BY column_order
            SQL,
            [
                'attributeCode' => $attributeCode,
            ]
        );
        $results = $statement->fetchAllAssociative();
        if ([] === $results) {
            throw TableConfigurationNotFoundException::forAttributeCode($attributeCode);
        }

        $platform = $this->connection->getDatabasePlatform();

        return $this->tableConfigurationFactory->createFromNormalized(
            array_map(
                fn (array $row): array => [
                    'id' => $row['id'],
                    'code' => $row['code'],
                    'data_type' => $row['data_type'],
                    'labels' => \json_decode($row['labels'], true),
                    'validations' => \json_decode($row['validations'], true),
                    'is_required_for_completeness' => Type::getType(Types::BOOLEAN)->convertToPhpValue($row['is_required_for_completeness'], $platform),
                ],
                $results
            )
        );
    }
}
