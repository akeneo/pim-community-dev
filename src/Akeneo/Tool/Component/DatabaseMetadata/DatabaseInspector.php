<?php
declare(strict_types=1);

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Akeneo\Tool\Component\DatabaseMetadata;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;

class DatabaseInspector
{
    /** @var Connection */
    private $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * Fetch the list of the tables created in the given schema.
     */
    public function getTableList(string $db_name): array
    {
        $sql = <<<"SQL"
select
    TABLE_NAME      as  table_name,
    TABLE_TYPE      as table_type
from information_schema.tables
where table_schema=?
order by table_name ASC
SQL;
        $result = $this->db->executeQuery($sql, [$db_name]);

        return $result->fetchAll(FetchMode::ASSOCIATIVE);
    }

    /**
     * Fetch the list of the columns of all the tables in the given schema.
     */
    public function getColumnInfo(string $db_name): array
    {
        $sql = <<<"SQL"
select
    TABLE_NAME      as table_name,
    COLUMN_NAME     as column_name,
    IS_NULLABLE     as is_nullable,
    COLUMN_TYPE     as column_type,
    COLUMN_KEY      as column_key
from information_schema.COLUMNS
where TABLE_SCHEMA=?
order by TABLE_NAME asc, ORDINAL_POSITION asc
SQL;

        $result = $this->db->executeQuery($sql, [$db_name]);

        return $result->fetchAll(FetchMode::ASSOCIATIVE);
    }

    /**
     * fetch the values stored in the given column ofn the given table.
     */
    public function getTableColumnValues(string $table, string $column): array
    {
        $sql = sprintf("select %s as value from %s order by 1 asc", $column, $table);
        $result = $this->db->executeQuery($sql, []);

        return $result->fetchAll(FetchMode::ASSOCIATIVE);
    }
}
