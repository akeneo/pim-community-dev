<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2023 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Tool\Bundle\StorageUtilsBundle\tests\integration;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

/**
 * This test exist in order to enforce the fact that we cannot create new columns on some table until we didn't do an OPTIMIZE TABLE on those table.
 *
 * Link to the incident related:
 *     - https://www.notion.so/akeneo/2023-06-01-MySQL-crash-loop-6796c8a1656c49daaa986aa53274bc71
 *     - https://akeneo.slack.com/archives/C05A8TT1UCW/p1686739172807109
 *
 * Actually making an OPTIMIZE table on those table create a MySQL error on some large instances due to the fact that it require a lot of space to do it.
 * When UCS migration will be finished we didn't need to do it anymore as it made a MySQL dump and restore because the table will be rebuilt
 */
class TableWithOldInstantColsCannotHaveNewColumnUntilUcsMigrationTest extends TestCase
{
    public function test_large_table_cannot_add_new_column()
    {
        $this->assertEquals(['identifier', 'code', 'asset_family_identifier', 'value_collection', 'created_at', 'updated_at'], $this->getColumns('akeneo_asset_manager_asset'));
        $this->assertEquals(['id', 'parent_id', 'family_variant_id', 'code', 'raw_values', 'created', 'updated', 'quantified_associations'], $this->getColumns('pim_catalog_product_model'));
        $this->assertEquals(['product_model_id','evaluated_at','scores', 'scores_partial_criteria'], $this->getColumns('pim_data_quality_insights_product_model_score'));
    }

    private function getColumns(string $tableName): array
    {
        $sql = <<<SQL
            SELECT COLUMN_NAME
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = :tableName
            ORDER BY ORDINAL_POSITION;
        SQL;

        return $this->getConnection()->executeQuery($sql, [
            'tableName' => $tableName,
        ])->fetchFirstColumn();
    }

    private function getConnection(): Connection
    {
        return $this->get('database_connection');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
