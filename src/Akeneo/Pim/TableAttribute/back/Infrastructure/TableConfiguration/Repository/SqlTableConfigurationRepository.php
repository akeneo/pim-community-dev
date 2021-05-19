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
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TableConfiguration;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TextColumn;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;

/**
 * @author Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 */
final class SqlTableConfigurationRepository implements TableConfigurationRepository
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function save(int $attributeId, TableConfiguration $tableConfiguration): void
    {
        if ($this->connection->isTransactionActive()) {
            $this->doSave($attributeId, $tableConfiguration);
            return;
        }

        $this->connection->transactional(fn () => $this->doSave($attributeId, $tableConfiguration));
    }

    private function doSave(int $attributeId, TableConfiguration $tableConfiguration): void
    {
        $newColumnCodes = array_map(
            fn (array $columnDefinition): string => $columnDefinition['code'],
            $tableConfiguration->normalize()
        );

        $this->connection->executeQuery(
            'DELETE FROM pim_catalog_table_column WHERE attribute_id = :attribute_id AND code NOT IN (:newColumnCodes)',
            [
                'attribute_id' => $attributeId,
                'newColumnCodes' => $newColumnCodes,
            ],
            [
                'newColumnCodes' => Connection::PARAM_STR_ARRAY,
            ]
        );
        foreach ($tableConfiguration->normalize() as $columnOrder => $column) {
            $this->connection->executeQuery(
                <<<SQL
                INSERT INTO pim_catalog_table_column (id, attribute_id, code, data_type, column_order, labels)
                VALUES (:id, :attribute_id, :code, :data_type, :column_order, :labels)
                ON DUPLICATE KEY UPDATE column_order = VALUES(column_order), labels = VALUES(labels)
                SQL,
                [
                    'id' =>  ($column['code'] . '_' . Uuid::uuid4()->toString()),
                    'attribute_id' => $attributeId,
                    'code' => $column['code'],
                    'data_type' => $column['data_type'],
                    'column_order' => $columnOrder,
                    'labels' => \json_encode($column['labels']),
                ]
            );
        }
    }

    public function getByAttributeId(int $attributeId): TableConfiguration
    {
        $statement = $this->connection->executeQuery(
            <<<SQL
            SELECT id, code, data_type, column_order, labels
            FROM pim_catalog_table_column
            WHERE attribute_id = :attributeId
            ORDER BY column_order
            SQL,
            [
                'attributeId' => $attributeId,
            ]
        );
        $results = $statement->fetchAll();

        return TableConfiguration::fromColumnDefinitions(
            array_map(
                fn (array $row): ColumnDefinition => TextColumn::fromNormalized([
                    'code' => $row['code'],
                    'labels' => \json_decode($row['labels'], true),
                ]),
                $results
            )
        );
    }
}
