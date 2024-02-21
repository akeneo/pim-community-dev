<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_7_0_20220404075823_add_uuid_columns extends AbstractMigration
{
    private const TABLES = [
        'pim_catalog_product' => 'uuid',
        'pim_catalog_association' => 'owner_uuid',
        'pim_catalog_association_product' => 'product_uuid',
        'pim_catalog_association_product_model_to_product' => 'product_uuid',
        'pim_catalog_category_product' => 'product_uuid',
        'pim_catalog_group_product' => 'product_uuid',
        'pim_catalog_product_unique_data' => 'product_uuid',
        'pim_catalog_completeness' => 'product_uuid',
        'pim_data_quality_insights_product_criteria_evaluation' => 'product_uuid',
        'pim_data_quality_insights_product_score' => 'product_uuid',
        'pimee_teamwork_assistant_completeness_per_attribute_group' => 'product_uuid',
        'pimee_teamwork_assistant_project_product' => 'product_uuid',
        'pimee_workflow_product_draft' => 'product_uuid',
        'pimee_workflow_published_product' => 'original_product_uuid',
        'pim_versioning_version' => 'resource_uuid',
    ];

    private const TABLE_WITHOUT_UUID_COMMENT = [
        'pim_catalog_completeness',
        'pim_data_quality_insights_product_criteria_evaluation',
        'pim_data_quality_insights_product_score',
        'pimee_teamwork_assistant_completeness_per_attribute_group',
        'pimee_teamwork_assistant_project_product',
        'pimee_workflow_product_draft',
        'pimee_workflow_published_product'
    ];

    public function getDescription(): string
    {
        return 'Add uuid columns for product table and every foreign table';
    }

    public function up(Schema $schema): void
    {
        foreach (self::TABLES as $tableName => $columnName) {
            if ($this->tableExists($tableName) && !$this->columnExists($tableName, $columnName)) {
                $this->addUuidColumn(
                    $tableName,
                    $columnName
                );
            }
        }
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }

    private function addUuidColumn(string $tableName, string $uuidColumName): void
    {
        $addUuidColumnSql = <<<SQL
            ALTER TABLE `{table_name}` ADD `{uuid_column_name}` BINARY(16) DEFAULT NULL {comment};
        SQL;

        $addUuidColumnQuery = \strtr(
            $addUuidColumnSql,
            [
                '{table_name}' => $tableName,
                '{uuid_column_name}' => $uuidColumName,
                '{comment}' => in_array($tableName, self::TABLE_WITHOUT_UUID_COMMENT) ? '' : 'COMMENT "(DC2Type:uuid_binary)"'
            ]
        );

        $this->addSql($addUuidColumnQuery);
    }

    private function tableExists(string $tableName): bool
    {
        $rows = $this->connection->fetchAllAssociative(
            <<<SQL
                SHOW TABLES LIKE :tableName
            SQL,
            ['tableName' => $tableName]
        );

        return count($rows) >= 1;
    }

    private function columnExists(string $tableName, string $columnName): bool
    {
        $rows = $this->connection->fetchAllAssociative(
            \strtr(
                <<<SQL
                    SHOW COLUMNS FROM {table_name} LIKE :columnName
                SQL,
                ['{table_name}' => $tableName]
            ),
            ['columnName' => $columnName]
        );

        return count($rows) >= 1;
    }
}
