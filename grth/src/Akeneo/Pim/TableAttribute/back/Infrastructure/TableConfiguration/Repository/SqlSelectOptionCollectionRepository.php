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
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\SelectOptionCode;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\WriteSelectOptionCollection;
use Doctrine\DBAL\Connection;

class SqlSelectOptionCollectionRepository implements SelectOptionCollectionRepository
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function save(
        string $attributeCode,
        ColumnCode $columnCode,
        WriteSelectOptionCollection $selectOptionCollection
    ): void {
        if ($this->connection->isTransactionActive()) {
            $this->doSave($attributeCode, $columnCode, $selectOptionCollection);

            return;
        }

        $this->connection->transactional(fn () => $this->doSave($attributeCode, $columnCode, $selectOptionCollection));
    }

    public function getByColumn(string $attributeCode, ColumnCode $columnCode): SelectOptionCollection
    {
        $sql = <<<SQL
        SELECT pim_catalog_table_column_select_option.* 
        FROM pim_catalog_table_column_select_option
            JOIN pim_catalog_table_column ON pim_catalog_table_column.id = pim_catalog_table_column_select_option.column_id
            JOIN pim_catalog_attribute pca on pim_catalog_table_column.attribute_id = pca.id
        WHERE pca.code = :attributeCode
            AND pim_catalog_table_column.code = :columnCode
SQL;

        $result = $this->connection->executeQuery($sql, [
            'attributeCode' => $attributeCode,
            'columnCode' => $columnCode->asString(),
        ])->fetchAllAssociative();

        $options = \array_map(static fn ($rawOption) => [
            'code' => $rawOption['code'],
            'labels' => \json_decode($rawOption['labels'], true),
        ], $result);

        return SelectOptionCollection::fromNormalized($options);
    }

    public function upsert(
        string $attributeCode,
        ColumnCode $columnCode,
        SelectOptionCollection $selectOptionCollection
    ): void {
        $this->persistOptions(
            $this->getColumnId($attributeCode, $columnCode),
            $selectOptionCollection->normalize()
        );
    }

    private function doSave(
        string $attributeCode,
        ColumnCode $columnCode,
        WriteSelectOptionCollection $selectOptionCollection
    ): void {
        $newOptionCodes = \array_map(
            fn (SelectOptionCode $optionCode): string => $optionCode->asString(),
            $selectOptionCollection->getOptionCodes()
        );
        $columnId = $this->getColumnId($attributeCode, $columnCode);
        if ($newOptionCodes === []) {
            $this->connection->executeQuery(
                <<<SQL
                DELETE table_column_option.* 
                FROM pim_catalog_table_column_select_option table_column_option                
                WHERE column_id = :column_id
                SQL,
                ['column_id' => $columnId]
            );

            return;
        }

        $this->connection->executeQuery(
            <<<SQL
            DELETE table_column_option.*
            FROM pim_catalog_table_column_select_option table_column_option                    
            WHERE column_id = :column_id 
            AND code NOT IN (:new_option_codes);                    
            SQL,
            [
                'column_id' => $columnId,
                'new_option_codes' => $newOptionCodes,
            ],
            [
                'new_option_codes' => Connection::PARAM_STR_ARRAY,
            ]
        );

        $this->persistOptions($columnId, $selectOptionCollection->normalize());
    }

    private function persistOptions(string $columnId, array $options): void
    {
        $placeholders = \implode(',', \array_fill(0, \count($options), '(?, ?, ?)'));
        $statement = $this->connection->prepare(
            <<<SQL
            INSERT INTO pim_catalog_table_column_select_option (column_id, code, labels)
            VALUES {$placeholders}
            ON DUPLICATE KEY UPDATE labels = VALUES(labels);
            SQL
        );

        $placeholderIndex = 0;
        foreach ($options as $normalizedSelectOption) {
            $statement->bindValue(++$placeholderIndex, $columnId);
            $statement->bindValue(++$placeholderIndex, $normalizedSelectOption['code']);
            $statement->bindValue(++$placeholderIndex, \json_encode($normalizedSelectOption['labels']));
        }

        $statement->executeQuery();
    }

    private function getColumnId(string $attributeCode, ColumnCode $columnCode): string
    {
        return $this->connection->executeQuery(
            <<<SQL
            SELECT table_column.id
                FROM pim_catalog_table_column table_column
                INNER JOIN pim_catalog_attribute attribute ON attribute.id = table_column.attribute_id
                WHERE table_column.code = :column_code AND attribute.code = :attribute_code
            SQL,
            [
                'attribute_code' => $attributeCode,
                'column_code' => $columnCode->asString(),
            ],
        )->fetchOne();
    }
}
