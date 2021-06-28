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

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ColumnDefinition;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Factory\ColumnFactory;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\SelectOptionCollectionRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationNotFoundException;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TableConfiguration;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;

/**
 * @author Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 */
final class SqlTableConfigurationRepository implements TableConfigurationRepository
{
    private Connection $connection;
    private ColumnFactory $columnFactory;
    private SelectOptionCollectionRepository $selectOptionCollectionRepository;

    public function __construct(Connection $connection, ColumnFactory $columnFactory, SelectOptionCollectionRepository $selectOptionCollectionRepository)
    {
        $this->connection = $connection;
        $this->columnFactory = $columnFactory;
        $this->selectOptionCollectionRepository = $selectOptionCollectionRepository;
    }

    private function getNextIdentifier(string $columnCode): string
    {
        return sprintf('%s_%s', $columnCode, Uuid::uuid4()->toString());
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
        $newColumnCodesAndTypes = array_map(
            fn (array $columnDefinition): string => $columnDefinition['code'] . '-' . $columnDefinition['data_type'],
            $tableConfiguration->normalize()
        );

        $this->connection->executeQuery(<<<SQL
            DELETE table_column.* FROM pim_catalog_table_column table_column
            INNER JOIN pim_catalog_attribute attribute ON attribute.id = table_column.attribute_id
            WHERE attribute.code = :attribute_code
                AND CONCAT(table_column.code, '-', table_column.data_type) NOT IN (:newColumnCodesAndTypes)
            SQL,
            [
                'attribute_code' => $attributeCode,
                'newColumnCodesAndTypes' => $newColumnCodesAndTypes,
            ],
            [
                'newColumnCodesAndTypes' => Connection::PARAM_STR_ARRAY,
            ]
        );
        foreach ($tableConfiguration->normalize() as $columnOrder => $columnDefinition) {
            $this->connection->executeQuery(
                <<<SQL
                INSERT INTO pim_catalog_table_column (id, attribute_id, code, data_type, column_order, labels, validations)
                SELECT * FROM (
                    SELECT :column_id, attribute.id, :code, :data_type, :column_order AS column_order, :labels AS labels, :validations as validations
                    FROM pim_catalog_attribute AS attribute WHERE code = :attribute_code
                ) AS newvalues                
                ON DUPLICATE KEY UPDATE column_order = newvalues.column_order, labels = newvalues.labels, validations = newvalues.validations
                SQL,
                [
                    'column_id' =>  $this->getNextIdentifier($columnDefinition['code']),
                    'attribute_code' => $attributeCode,
                    'code' => $columnDefinition['code'],
                    'data_type' => $columnDefinition['data_type'],
                    'column_order' => $columnOrder,
                    'labels' => \json_encode($columnDefinition['labels']),
                    'validations' => \json_encode($columnDefinition['validations']),
                ]
            );
        }
        foreach ($tableConfiguration->getSelectColumns() as $column) {
            $this->selectOptionCollectionRepository->save($attributeCode, $column->code(), $column->optionCollection());
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
                JSON_ARRAYAGG(
                    CASE
                        WHEN select_option.code IS NULL THEN null
                        ELSE JSON_OBJECT('code', select_option.code, 'labels', select_option.labels)
                    END
                ) as options
            FROM pim_catalog_table_column table_column
                INNER JOIN pim_catalog_attribute attribute ON attribute.id = table_column.attribute_id
                LEFT JOIN pim_catalog_table_column_select_option select_option ON select_option.column_id = table_column.id
            WHERE attribute.code = :attributeCode
            GROUP BY table_column.id, table_column.code, data_type, column_order, labels, validations
            ORDER BY column_order
            SQL,
            [
                'attributeCode' => $attributeCode,
            ]
        );
        $results = $statement->fetchAll();
        if (0 === count($results)) {
            throw TableConfigurationNotFoundException::forAttributeCode($attributeCode);
        }

        return TableConfiguration::fromColumnDefinitions(
            array_map(
                fn (array $row): ColumnDefinition => $this->columnFactory->createFromNormalized(
                    [
                        'code' => $row['code'],
                        'data_type' => $row['data_type'],
                        'labels' => \json_decode($row['labels'], true),
                        'validations' => \json_decode($row['validations'], true),
                        'options' => \array_filter(\json_decode($row['options'], true)),
                    ]
                ),
                $results
            )
        );
    }
}
