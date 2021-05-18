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

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TableConfiguration;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TextColumn;
use Doctrine\DBAL\Connection;

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
        $isTransactionActive = $this->connection->isTransactionActive();
        if (!$isTransactionActive) {
            $this->connection->beginTransaction();
        }
        try {
            $this->connection->executeQuery(
                'DELETE FROM pim_catalog_table_column WHERE attribute_id = :attribute_id',
                ['attribute_id' => $attributeId]
            );
            foreach ($tableConfiguration->normalize() as $columnOrder => $column) {
                $this->connection->executeQuery(
                    'INSERT INTO pim_catalog_table_column (id, attribute_id, code, data_type, column_order) VALUES (:id, :attribute_id, :code, :data_type, :column_order)',
                    [
                        'id' => uniqid($column['code'] . '_'),
                        'attribute_id' => $attributeId,
                        'code' => $column['code'],
                        'data_type' => $column['data_type'],
                        'column_order' => $columnOrder,
                    ]
                );
            }

            if (!$isTransactionActive) {
                $this->connection->commit();
            }
        } catch (\Exception $e) {
            if (!$isTransactionActive) {
                $this->connection->rollBack();
            }

            throw $e;
        }
    }

    public function getByAttributeId(int $attributeId): TableConfiguration
    {
        return TableConfiguration::fromColumnDefinitions([
            TextColumn::fromNormalized(['code' => 'ingredients', 'type' => 'text']),
            TextColumn::fromNormalized(['code' => 'quantity', 'type' => 'text']),
        ]);
    }
}
