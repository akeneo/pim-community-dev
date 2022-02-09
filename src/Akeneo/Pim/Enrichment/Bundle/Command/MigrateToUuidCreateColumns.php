<?php

namespace Akeneo\Pim\Enrichment\Bundle\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateToUuidCreateColumns implements MigrateToUuidStep
{
    public const TABLES = [
        'pim_catalog_product' => ['id', 'uuid'],
        'pim_catalog_association' => ['owner_id', 'owner_uuid'],
        'pim_catalog_association_product' => ['product_id', 'product_uuid'],
        'pim_catalog_association_product_model_to_product' => ['product_id', 'product_uuid'],
        'pim_catalog_category_product' => ['product_id', 'product_uuid'],
        'pim_catalog_group_product' => ['product_id', 'product_uuid'],
        'pim_catalog_product_unique_data' => ['product_id', 'product_uuid'],
        'pim_data_quality_insights_product_criteria_evaluation' => ['product_id', 'product_uuid'],
        'pim_data_quality_insights_product_score' => ['product_id', 'product_uuid'],
        'pimee_teamwork_assistant_completeness_per_attribute_group' => ['product_id', 'product_uuid'],
        'pimee_teamwork_assistant_project_product' => ['product_id', 'product_uuid'],
        'pimee_workflow_product_draft' => ['product_id', 'product_uuid'],
        'pimee_workflow_published_product' => ['original_product_id', 'original_product_uuid'],
        'pim_versioning_version' => ['resource_id', 'resource_uuid'],
    ];

    public function __construct(private Connection $dbConnection)
    {
    }

    public function getMissingCount(OutputInterface $output): int
    {
        $count = 0;
        foreach (self::TABLES as $tableName => $columnNames) {
            if ($this->tableExists($tableName) && !$this->columnExists($tableName, $columnNames[1])) {
                $output->writeln(sprintf('... missing %s', $tableName));
                $count++;
            }
        }

        return $count;
    }

    public function addMissing(OutputInterface $output): void
    {
        foreach (self::TABLES as $tableName => $columnNames) {
            if ($this->tableExists($tableName) && !$this->columnExists($tableName, $columnNames[1])) {
                $output->writeln(sprintf('... add %s', $tableName));
                $addUuidColumnQuery = sprintf(<<<SQL
    ALTER TABLE `%s` ADD `%s` BINARY(16) DEFAULT NULL AFTER `%s`, LOCK=NONE, ALGORITHM=INPLACE;
    SQL, $tableName, $columnNames[1], $columnNames[0]);

                $this->dbConnection->executeQuery($addUuidColumnQuery);
            }
        }
    }

    private function columnExists(string $tableName, string $columnName): bool
    {
        $rows = $this->dbConnection->fetchAllAssociative(sprintf('SHOW COLUMNS FROM %s LIKE :columnName', $tableName),
            [
                'columnName' => $columnName,
            ]);

        return count($rows) >= 1;
    }

    private function tableExists(string $tableName): bool
    {
        $rows = $this->dbConnection->fetchAllAssociative(
            'SHOW TABLES LIKE :tableName',
            [
                'tableName' => $tableName,
            ]
        );

        return count($rows) >= 1;
    }
}
