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
    private const EXCLUDED_TABLES = [
        'acme_reference_data_color',
        'acme_reference_data_fabric',
        'pimee_product_asset_asset',
        'pimee_product_asset_asset_category',
        'pimee_product_asset_asset_tag',
        'pimee_product_asset_category',
        'pimee_product_asset_category_translation',
        'pimee_product_asset_channel_variation_configuration',
        'pimee_product_asset_file_metadata',
        'pimee_product_asset_reference',
        'pimee_product_asset_tag',
        'pimee_product_asset_variation',
        'pimee_security_asset_category_access'
    ];

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
        $sql = <<<SQL
SELECT
    TABLE_NAME      as  table_name,
    TABLE_TYPE      as table_type
FROM information_schema.tables
WHERE table_schema = :table_schema
AND TABLE_NAME NOT IN (:excluded_tables)
ORDER BY table_name ASC
SQL;
        $result = $this->db->executeQuery(
            $sql,
            ["table_schema" => $db_name, "excluded_tables" => self::EXCLUDED_TABLES],
            ["excluded_tables" => Connection::PARAM_STR_ARRAY]
        );

        return $result->fetchAllAssociative();
    }

    /**
     * Fetch the list of the columns of all the tables in the given schema.
     */
    public function getColumnInfo(string $db_name): array
    {
        $sql = <<<SQL
SELECT
    TABLE_NAME      as table_name,
    COLUMN_NAME     as column_name,
    IS_NULLABLE     as is_nullable,
    COLUMN_TYPE     as column_type,
    COLUMN_KEY      as column_key
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = :table_schema
AND TABLE_NAME NOT IN (:excluded_tables)
ORDER BY TABLE_NAME ASC, ORDINAL_POSITION ASC
SQL;

        $result = $this->db->executeQuery(
            $sql,
            ["table_schema" => $db_name, "excluded_tables" => self::EXCLUDED_TABLES],
            ["excluded_tables" => Connection::PARAM_STR_ARRAY]
        );

        return $result->fetchAllAssociative();
    }

    /**
     * fetch the values stored in the given column of the given table.
     */
    public function getTableColumnValues(string $table, string $column): array
    {
        $sql = sprintf("select %s as value from %s order by 1 asc", $column, $table);
        $result = $this->db->executeQuery($sql, []);

        return $result->fetchAllAssociative();
    }
}
