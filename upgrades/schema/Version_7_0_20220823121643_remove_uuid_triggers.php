<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\Migrations\Exception\IrreversibleMigration;

/**
 * Remove triggers added in the UUID migration which add triggers on foreign uuid column.
 * @see https://github.com/akeneo/pim-community-dev/blob/77be7d26721554834bbbabae39bf6f11a90f77ac/src/Akeneo/Pim/Enrichment/Bundle/Command/MigrateToUuid/MigrateToUuidAddTriggers.php#L15
 */
final class Version_7_0_20220823121643_remove_uuid_triggers extends AbstractMigration
{
    public const TRIGGERS_TO_REMOVE = [
        'pim_catalog_association_uuid_insert',
        'pim_catalog_association_uuid_update',
        'pim_catalog_association_product_uuid_insert',
        'pim_catalog_association_product_uuid_update',
        'pim_catalog_association_product_model_to_product_uuid_insert',
        'pim_catalog_association_product_model_to_product_uuid_update',
        'pim_catalog_category_product_uuid_insert',
        'pim_catalog_category_product_uuid_update',
        'pim_catalog_group_product_uuid_insert',
        'pim_catalog_group_product_uuid_update',
        'pim_catalog_product_unique_data_uuid_insert',
        'pim_catalog_product_unique_data_uuid_update',
        'pim_catalog_completeness_uuid_insert',
        'pim_catalog_completeness_uuid_update',
        'pim_dqi_product_criteria_evaluation_uuid_insert',
        'pim_dqi_product_criteria_evaluation_uuid_update',
        'pim_dqi_product_score_uuid_insert',
        'pim_dqi_product_score_uuid_update',
        'pim_versioning_version_uuid_insert',
        'pim_versioning_version_uuid_update',
        'pim_comment_comment_uuid_insert',
        'pim_comment_comment_uuid_update',
        'pimee_workflow_product_draft_uuid_insert',
        'pimee_workflow_product_draft_uuid_update',
        'pimee_workflow_published_product_uuid_insert',
        'pimee_workflow_published_product_uuid_update',
        'pimee_twa_completeness_per_attribute_group_uuid_insert',
        'pimee_twa_completeness_per_attribute_group_uuid_update',
        'pimee_twa_project_product_uuid_insert',
        'pimee_twa_project_product_uuid_update',
    ];
    private const TABLES_TO_UPDATE = [

        'pim_catalog_category_product' => 'product_id',
        'pim_catalog_group_product' => 'product_id',
        'pim_catalog_product_unique_data' => 'product_id',
        'pim_catalog_completeness' => 'product_id',
        'pim_data_quality_insights_product_criteria_evaluation' => 'product_id',
        'pim_data_quality_insights_product_score' => 'product_id',
        'pimee_teamwork_assistant_completeness_per_attribute_group' => 'product_id',
        'pimee_teamwork_assistant_project_product' => 'product_id',
        'pimee_workflow_product_draft' => 'product_id',
        'pimee_workflow_published_product' => 'original_product_id',
        'pim_catalog_association' => 'owner_id',
        'pim_catalog_association_product' => 'product_id',
        'pim_catalog_association_product_model_to_product' => 'product_id',
    ];

    public function getDescription(): string
    {
        return 'Remove UUID triggers and product id related columns';
    }

    public function up(Schema $schema): void
    {
        $this->dropTriggers();
        $this->dropProductIdColumns();
    }

    public function down(Schema $schema): void
    {
        throw new IrreversibleMigration();
    }

    private function dropTriggers(): void
    {
        foreach (self::TRIGGERS_TO_REMOVE as $triggerToRemove) {
            $this->addSql(sprintf('DROP TRIGGER IF EXISTS %s', $triggerToRemove));
        }
    }

    private function dropProductIdColumns(): void
    {
        foreach (self::TABLES_TO_UPDATE as $table => $column) {
            if ($this->columnExists($table, $column)) {
                $this->dropForeignKeys($table, $column);
                $this->dropUniqueConstraints($table, $column);
                $this->dropColumn($table, $column);
            }
        }
    }

    private function columnExists(string $table, string $columnName): bool
    {
        $tableColumnNames = array_map(
            static fn(Column $column) => $column->getName(),
            $this->connection->getSchemaManager()->listTableColumns($table)
        );

        return in_array($columnName, $tableColumnNames);
    }

    public function dropForeignKeys(string $table, string $column): void
    {
        $tableDetails = $this->connection->getSchemaManager()->listTableDetails($table);
        $foreignKeys = $tableDetails->getForeignKeys();
        foreach ($foreignKeys as $foreignKey) {
            if (in_array($column, $foreignKey->getLocalColumns())) {
                $this->addSql(sprintf('ALTER TABLE %s DROP FOREIGN KEY %s', $table, $foreignKey->getName()));
            }
        }
    }

    private function dropUniqueConstraints(string $table, string $column): void
    {
        $tableDetails = $this->connection->getSchemaManager()->listTableDetails($table);
        $indexes = $tableDetails->getIndexes();
        foreach ($indexes as $index) {
            if (in_array($column, $index->getColumns())) {
                $this->addSql(sprintf('DROP INDEX %s ON %s', $index->getName(), $table));
            }
        }

    }

    private function dropColumn(string $table, string $column): void
    {
        $this->addSql(sprintf('ALTER TABLE %s DROP COLUMN %s', $table, $column));
    }
}
