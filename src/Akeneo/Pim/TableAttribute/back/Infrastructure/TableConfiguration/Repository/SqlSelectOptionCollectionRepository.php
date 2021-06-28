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

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\SelectOptionCollectionRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectOptionCollection;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
use Doctrine\DBAL\Connection;

class SqlSelectOptionCollectionRepository implements SelectOptionCollectionRepository
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritDoc}
     */
    public function save(string $attributeCode, ColumnCode $columnCode, SelectOptionCollection $selectOptionCollection): void
    {
        if ($this->connection->isTransactionActive()) {
            $this->doSave($attributeCode, $columnCode, $selectOptionCollection);
            return;
        }

        $this->connection->transactional(fn () => $this->doSave($attributeCode, $columnCode, $selectOptionCollection));
    }

    private function doSave(
        string $attributeCode,
        ColumnCode $columnCode,
        SelectOptionCollection $selectOptionCollection
    ): void {
        $newAttributeAndOptionCodes = array_map(
            fn (string $optionCode): string => sprintf(
                '%s-%s-%s',
                $attributeCode,
                $columnCode->asString(),
                $optionCode
            ),
            $selectOptionCollection->getOptionCodes()
        );
        $this->connection->executeQuery(<<<SQL
            DELETE table_column_option.* FROM pim_catalog_table_column_select_option table_column_option
                INNER JOIN pim_catalog_table_column table_column ON table_column.id = table_column_option.column_id
                INNER JOIN pim_catalog_attribute attribute ON attribute.id = table_column.attribute_id
            WHERE table_column.code = :column_code
                AND CONCAT(attribute.code, '-', table_column.code, '-', table_column_option.code) NOT IN (:newAttributeAndOptionCodes)
            SQL,
            [
                'column_code' => $columnCode->asString(),
                'newAttributeAndOptionCodes' => $newAttributeAndOptionCodes,
            ],
            [
                'newAttributeAndOptionCodes' => Connection::PARAM_STR_ARRAY,
            ]
        );

        foreach ($selectOptionCollection->normalize() as $selectOption) {
            $this->connection->executeQuery(
                <<<SQL
                INSERT INTO pim_catalog_table_column_select_option (column_id, code, labels)
                SELECT * FROM (
                    SELECT table_column.id, :code, :labels
                    FROM pim_catalog_table_column table_column
                        INNER JOIN pim_catalog_attribute attribute ON attribute.id = table_column.attribute_id
                    WHERE table_column.code = :column_code AND attribute.code = :attribute_code
                ) AS newvalues                
                ON DUPLICATE KEY UPDATE labels = :labels
                SQL,
                [
                    'attribute_code' => $attributeCode,
                    'column_code' => $columnCode->asString(),
                    'code' => $selectOption['code'],
                    'labels' => \json_encode($selectOption['labels']),
                ]
            );
        }
    }
}
