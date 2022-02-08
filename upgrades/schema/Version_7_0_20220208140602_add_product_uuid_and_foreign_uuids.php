<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_7_0_20220208140602_add_product_uuid_and_foreign_uuids extends AbstractMigration
{
    private const PRODUCT_TABLE_NAME = 'pim_catalog_product';
    private const FOREIGN_TABLE_NAMES_AND_COLUMN_NAMES = [
        'pim_catalog_association' => 'owner_id',
        'pim_catalog_association_product' => 'product_id', # TODO
        'pim_catalog_association_product_model_to_product' => 'product_id', #TODO
        'pim_catalog_category_product' => 'product_id', #TODO
        'pim_catalog_group_product' => 'product_id', #TODO
        'pim_catalog_product_unique_data' => 'product_id',
        'pim_data_quality_insights_product_criteria_evaluation' => 'product_id',
        'pim_data_quality_insights_product_score' => 'product_id',
        'pimee_teamwork_assistant_completeness_per_attribute_group' => 'product_id',
        'pimee_teamwork_assistant_project_product' => 'product_id',
        'pimee_workflow_product_draft' => 'product_id',
        'pimee_workflow_published_product' => 'original_product_id'];

    public function getDescription(): string
    {
        return 'Adds an empty "uuid" column to the products table and adds an empty foreign column to all the linked tables';
    }

    public function up(Schema $schema): void
    {
        if (!$this->columnExists(self::PRODUCT_TABLE_NAME, 'uuid')) {
            $this->addColumn(self::PRODUCT_TABLE_NAME, 'uuid', 'id');
        }

        foreach (self::FOREIGN_TABLE_NAMES_AND_COLUMN_NAMES as $foreignTable => $idColumnName) {
            $uuidColumnName = str_replace('_id', '_uuid', $idColumnName);
            if ($this->tableExists($foreignTable) && !$this->columnExists($foreignTable, $uuidColumnName)) {
                $this->addColumn($foreignTable, $uuidColumnName, $idColumnName);
            }
        }
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }

    private function columnExists(string $tableName, string $columnName): bool
    {
        $rows = $this->connection->executeQuery(
            sprintf('SHOW COLUMNS FROM %s LIKE :columnName', $tableName),
            [
                'columnName' => $columnName,
            ]
        )->fetchAllAssociative();

        return count($rows) >= 1;
    }

    private function tableExists(string $tableName): bool
    {
        $rows = $this->connection->executeQuery(
            'SHOW TABLES LIKE :tableName',
            [
                'tableName' => $tableName,
            ]
        )->fetchAllAssociative();

        return count($rows) >= 1;
    }

    private function addColumn(string $tableName, string $uuidColumnName, string $idColumnName): void
    {
        $addForeignProductUuid = sprintf(
            'ALTER TABLE `%s` ADD `%s` VARBINARY(16) DEFAULT NULL AFTER `%s`, LOCK=NONE, ALGORITHM=INPLACE;',
            $tableName,
            $uuidColumnName,
            $idColumnName
        );

        $this->addSql($addForeignProductUuid);
    }
}
