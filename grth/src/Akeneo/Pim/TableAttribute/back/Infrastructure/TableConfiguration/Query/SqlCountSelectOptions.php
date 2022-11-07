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

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Query\CountSelectOptions;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
use Doctrine\DBAL\Connection;

final class SqlCountSelectOptions implements CountSelectOptions
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function all(): int
    {
        return (int) $this->connection->executeQuery(
            'SELECT COUNT(*) FROM pim_catalog_table_column_select_option'
        )->fetchOne();
    }

    public function forAttributeAndColumn(string $attributeCode, ColumnCode $columnCode): int
    {
        $sql = <<<SQL
        SELECT
            count(*)
        FROM pim_catalog_table_column_select_option o
            JOIN pim_catalog_table_column c ON c.id = o.column_id
            JOIN pim_catalog_attribute a ON a.id = c.attribute_id
        WHERE a.code = :attribute_code AND c.code = :column_code
        SQL;

        return (int) $this->connection->executeQuery(
            $sql,
            ['attribute_code' => $attributeCode, 'column_code' => $columnCode->asString()]
        )->fetchOne();
    }
}
