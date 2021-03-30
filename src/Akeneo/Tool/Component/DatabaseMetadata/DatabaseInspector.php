<?php declare(strict_types=1);

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

    public function getTableList(): array
    {
        $sql = <<<"SQL"
select
    TABLE_NAME      as  table_name,
    TABLE_TYPE      as table_type,
    AUTO_INCREMENT IS NOT NULL AND AUTO_INCREMENT > 0  as auto_increment
from information_schema.tables
where table_schema=?
order by table_name ASC
SQL;
        $result = $this->db->executeQuery($sql, ["akeneo_pim"]);

        return $result->fetchAll(FetchMode::ASSOCIATIVE);
    }

    public function getColumnInfo(): array
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

        $result = $this->db->executeQuery($sql, ["akeneo_pim"]);

        return $result->fetchAll(FetchMode::ASSOCIATIVE);
    }

    public function getTableValues(string $table, string $column): array
    {
        $sql = sprintf("select %s as value from %s order by 1 asc", $column, $table);
        $result = $this->db->executeQuery($sql, []);

        return $result->fetchAll(FetchMode::ASSOCIATIVE);
    }
}