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

namespace Akeneo\Pim\TableAttribute\Infrastructure\TableConfiguration\Query;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Query\GetNonExistingSelectOptionCodes;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\SelectOptionCode;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;

class SqlGetNonExistingSelectOptionCodes implements GetNonExistingSelectOptionCodes
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function forOptionCodes(string $attributeCode, ColumnCode $columnCode, array $selectOptionCodes): array
    {
        if ($selectOptionCodes === []) {
            return [];
        }

        $selectStringOptionCodes = array_map(
            fn (SelectOptionCode $selectOptionCode): string => $selectOptionCode->asString(),
            $selectOptionCodes
        );

        $sql = <<<SQL
        SELECT pim_catalog_table_column_select_option.code
        FROM pim_catalog_table_column_select_option
            JOIN pim_catalog_table_column ON pim_catalog_table_column.id = pim_catalog_table_column_select_option.column_id
            JOIN pim_catalog_attribute ON pim_catalog_attribute.id = pim_catalog_table_column.attribute_id
        WHERE pim_catalog_table_column.code = :columnCode
            AND pim_catalog_attribute.code = :attributeCode
            AND pim_catalog_table_column_select_option.code IN (:selectOptionCodes)
SQL;

        $existingOptionCodes = $this->connection->executeQuery($sql, [
            'columnCode' => $columnCode->asString(),
            'attributeCode' => $attributeCode,
            'selectOptionCodes' => array_unique($selectStringOptionCodes),
        ], [
            ':selectOptionCodes' => Connection::PARAM_STR_ARRAY,
        ])->fetchFirstColumn();

        return \array_udiff(
            $selectOptionCodes,
            array_map(
                fn (string $code): SelectOptionCode => SelectOptionCode::fromString($code),
                $existingOptionCodes
            ),
            fn (SelectOptionCode $a, SelectOptionCode $b): int => \strcasecmp($a->asString(), $b->asString())
        );
    }
}
