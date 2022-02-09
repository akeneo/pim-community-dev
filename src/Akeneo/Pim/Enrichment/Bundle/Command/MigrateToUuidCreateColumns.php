<?php

namespace Akeneo\Pim\Enrichment\Bundle\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateToUuidCreateColumns
{
    private const TABLES = [
        'pim_catalog_product' => 'id',
        'pim_catalog_association' => 'owner_id',
        'pim_catalog_association_product' => 'product_id',
        'pim_catalog_association_product_model_to_product' => 'product_id',
        'pim_catalog_category_product' => 'product_id',
        'pim_catalog_group_product' => 'product_id',
        'pim_catalog_product_unique_data' => 'product_id',
        'pim_data_quality_insights_product_criteria_evaluation' => 'product_id',
        'pim_data_quality_insights_product_score' => 'product_id',
        'pimee_teamwork_assistant_completeness_per_attribute_group' => 'product_id',
        'pimee_teamwork_assistant_project_product' => 'product_id',
        'pimee_workflow_product_draft' => 'product_id',
        'pimee_workflow_published_product' => 'original_product_id'
    ];

    public function __construct(private Connection $dbConnection)
    {
    }

    public function getMissingCount(OutputInterface $output): int
    {
        $count = 0;
        foreach (self::TABLES as $tableName => $idColumnName) {
            $uuidColumnName = $this->getUuidColumnName($idColumnName);
            if ($this->tableExists($tableName) && !$this->columnExists($tableName, $uuidColumnName)) {
                $output->writeln(sprintf('... missing %s', $tableName));
                $count++;
            }
        }

        return $count;
    }

    public function addMissing(OutputInterface $output): void
    {
        foreach (self::TABLES as $tableName => $idColumnName) {
            $uuidColumnName = $this->getUuidColumnName($idColumnName);
            if ($this->tableExists($tableName) && !$this->columnExists($tableName, $uuidColumnName)) {
                $output->writeln(sprintf('... add %s', $tableName));
                $addUuidColumnQuery = sprintf(<<<SQL
    ALTER TABLE `%s` ADD `%s` BINARY(16) DEFAULT NULL AFTER `%s`, LOCK=NONE, ALGORITHM=INPLACE;
    SQL, $tableName, $uuidColumnName, $idColumnName);

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

    private function getUuidColumnName(string $columnName): string
    {
        return preg_replace('/(.*)id$/', '$1uuid', $columnName);
    }
}
