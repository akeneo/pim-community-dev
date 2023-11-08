<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Command\ZddMigrations;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Platform\Bundle\InstallerBundle\Command\ZddMigration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Column;
use Psr\Log\LoggerInterface;

/**
 * Removes
 *  - the triggers created during the UUID migration
 *  - the product id columns
 *
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class V20220729171405DropProductIdColumnsAndCleanVersioningResourceUuidColumns implements ZddMigration
{
    public const TABLES_TO_UPDATE = [
        'pim_catalog_category_product' => [
            'column' => 'product_id',
            'triggers' => [
                'pim_catalog_category_product_uuid_insert',
                'pim_catalog_category_product_uuid_update',
            ],
        ],
        'pim_catalog_group_product' => [
            'column' => 'product_id',
            'triggers' => [
                'pim_catalog_group_product_uuid_insert',
                'pim_catalog_group_product_uuid_update',
            ],
        ],
        'pim_catalog_product_unique_data' => [
            'column' => 'product_id',
            'triggers' => [
                'pim_catalog_product_unique_data_uuid_insert',
                'pim_catalog_product_unique_data_uuid_update',
            ],
        ],
        'pim_catalog_completeness' => [
            'column' => 'product_id',
            'triggers' => [
                'pim_catalog_completeness_uuid_insert',
                'pim_catalog_completeness_uuid_update',
            ],
        ],
        'pim_data_quality_insights_product_criteria_evaluation' => [
            'column' => 'product_id',
            'triggers' => [
                'pim_dqi_product_criteria_evaluation_uuid_insert',
                'pim_dqi_product_criteria_evaluation_uuid_update',
            ],
        ],
        'pim_data_quality_insights_product_score' => [
            'column' => 'product_id',
            'triggers' => [
                'pim_dqi_product_score_uuid_insert',
                'pim_dqi_product_score_uuid_update',
            ],
        ],
        'pimee_teamwork_assistant_completeness_per_attribute_group' => [
            'column' => 'product_id',
            'triggers' => [
                'pimee_twa_completeness_per_attribute_group_uuid_insert',
                'pimee_twa_completeness_per_attribute_group_uuid_update',
            ],
        ],
        'pimee_teamwork_assistant_project_product' => [
            'column' => 'product_id',
            'triggers' => [
                'pimee_twa_project_product_uuid_insert',
                'pimee_twa_project_product_uuid_update',
            ],
        ],
        'pimee_workflow_product_draft' => [
            'column' => 'product_id',
            'triggers' => [
                'pimee_workflow_product_draft_uuid_insert',
                'pimee_workflow_product_draft_uuid_update',
            ],
        ],
        'pimee_workflow_published_product' => [
            'column' => 'original_product_id',
            'triggers' => [
                'pimee_workflow_published_product_uuid_insert',
                'pimee_workflow_published_product_uuid_update',
            ],
        ],
        'pim_catalog_association' => [
            'column' => 'owner_id',
            'triggers' => [
                'pim_catalog_association_uuid_insert',
                'pim_catalog_association_uuid_update',
            ],
        ],
        'pim_catalog_association_product' => [
            'column' => 'product_id',
            'triggers' => [
                'pim_catalog_association_product_uuid_insert',
                'pim_catalog_association_product_uuid_update',
            ],
        ],
        'pim_catalog_association_product_model_to_product' => [
            'column' => 'product_id',
            'triggers' => [
                'pim_catalog_association_product_model_to_product_uuid_insert',
                'pim_catalog_association_product_model_to_product_uuid_update',
            ],
        ],
        'pim_comment_comment' => [
            'column' => null,
            'triggers' => [
                'pim_comment_comment_uuid_insert',
                'pim_comment_comment_uuid_update',
            ],
        ],
        'pim_versioning_version' => [
            'column' => null,
            'triggers' => [
                'pim_versioning_version_uuid_insert',
                'pim_versioning_version_uuid_update',
            ],
        ],
    ];

    private bool $shouldLog = true;

    public function __construct(private Connection $connection, private LoggerInterface $logger)
    {
    }

    public function getName(): string
    {
        return 'DropProductIdColumnsAndCleanVersioningResourceUuidColumns';
    }

    public function migrate(): void
    {
        $this->log(sprintf('Starting %s', $this->getName()));
        foreach (self::TABLES_TO_UPDATE as $table => $properties) {
            $this->dropTriggersAndProductIdColumns($table, $properties);
        }
        $this->cleanVersioningResourceUuid();
        $this->log(sprintf('%s ended', $this->getName()));
    }


    public function migrateNotZdd(): void
    {
        $this->shouldLog = false;
        foreach (self::TABLES_TO_UPDATE as $table => $properties) {
            $this->dropTriggersAndProductIdColumns($table, $properties, false);
        }
        $this->cleanVersioningResourceUuid();
    }

    private function dropTriggersAndProductIdColumns(string $table, array $tableProperties, $isZdd = true): void
    {
        if (!$this->tableExists($table)) {
            $this->log(\sprintf('The %s table does not exist, moving on to the next table', $table));

            return;
        }

        $column = $tableProperties['column'] ?? null;

        if (null !== $column && $this->columnExists($table, $column)) {
            $this->dropForeignKeys($table, $column);
            $this->dropIndexes($table, $column);
            $this->setColumnNullable($table, $column, $isZdd);
            $this->dropTriggers($tableProperties['triggers']);
            $this->dropColumn($table, $column, $isZdd);
        } else {
            $this->dropTriggers($tableProperties['triggers']);
        }
    }

    private function cleanVersioningResourceUuid(): void
    {
        $totalVersionsCleaned = 0;
        $this->log('Start cleaning pim_versioning_version table');
        do {
            $versionIdsToClean = $this->getVersionIdsToClean();
            $this->cleanVersions($versionIdsToClean);
            $totalVersionsCleaned += \count($versionIdsToClean);
            $this->log(\sprintf('Rows cleaned so far: %d', $totalVersionsCleaned));
        } while ([] !== $versionIdsToClean);

        $this->log(sprintf('Successfully cleaned a total of %d versions', $totalVersionsCleaned));
    }

    private function tableExists(string $table): bool
    {
        return $this->connection->getSchemaManager()->tablesExist([$table]);
    }

    private function columnExists(string $table, string $columnName): bool
    {
        $tableColumnNames = array_map(
            static fn (Column $column) => $column->getName(),
            $this->connection->getSchemaManager()->listTableColumns($table)
        );

        return \in_array($columnName, $tableColumnNames);
    }

    private function dropTriggers(array $triggers): void
    {
        foreach ($triggers as $triggerToRemove) {
            $this->connection->executeStatement(
                \sprintf('DROP TRIGGER IF EXISTS %s', $triggerToRemove)
            );
            $this->log(
                sprintf('Dropped trigger %s', $triggerToRemove)
            );
        }
    }

    public function dropForeignKeys(string $table, string $column): void
    {
        $foreignKeys = $this->connection->getSchemaManager()->listTableForeignKeys($table);
        foreach ($foreignKeys as $foreignKey) {
            if (\in_array($column, $foreignKey->getLocalColumns())) {
                $this->connection->getSchemaManager()->dropForeignKey($foreignKey, $table);
                $this->log(
                    \sprintf('Dropped %s foreign key constraint on %s', $foreignKey->getName(), $table)
                );
            }
        }
    }

    private function dropIndexes(string $table, string $column): void
    {
        $indexes = $this->connection->getSchemaManager()->listTableIndexes($table);
        foreach ($indexes as $index) {
            if (\in_array($column, $index->getColumns())) {
                $this->connection->getSchemaManager()->dropIndex($index->getName(), $table);
                $this->log(
                    \sprintf('Dropped %s index on %s', $index->getName(), $table)
                );
            }
        }
    }

    private function setColumnNullable(string $table, string $column, bool $isZdd = true): void
    {
        if ($this->isColumnNullable($table, $column)) {
            $this->log(\sprintf('Skip: the %s.%s column is already nullable.', $table, $column));

            return;
        }

        $algorithm = $isZdd ? ', ALGORITHM=INPLACE' : '';
        $lock = $isZdd ? ', LOCK=NONE' : '';

        $this->connection->executeStatement(
            \sprintf('ALTER TABLE %s MODIFY %s INT DEFAULT NULL%s%s;', $table, $column, $algorithm, $lock)
        );
        $this->log(\sprintf('Set column nullable: %s.%s', $table, $column));
    }

    private function dropColumn(string $table, string $column, bool $isZdd = true): void
    {
        $algorithm = $isZdd ? ', ALGORITHM=INPLACE' : '';
        $lock = $isZdd ? ', LOCK=NONE' : '';

        $this->connection->executeStatement(
            \sprintf('ALTER TABLE %s DROP COLUMN %s%s%s;', $table, $column, $algorithm, $lock)
        );
        $this->log(\sprintf('Dropped column %s.%s', $table, $column));
    }

    private function cleanVersions(array $versionIdsToClean): void
    {
        $emptyResourceUuidQuery = <<<SQL
            UPDATE pim_versioning_version 
            SET resource_uuid = NULL, resource_name = resource_name 
            WHERE id IN (:version_ids);
            SQL;
        $this->connection->executeStatement(
            $emptyResourceUuidQuery,
            ['version_ids' => $versionIdsToClean],
            ['version_ids' => Connection::PARAM_INT_ARRAY]
        );
    }

    private function getVersionIdsToClean(): array
    {
        $sql = <<<SQL
            SELECT id
            FROM pim_versioning_version
            WHERE
                resource_uuid IS NOT NULL
                AND resource_name != :resource_name
            LIMIT 1000;
            SQL;

        return $this->connection->fetchFirstColumn(
            $sql,
            ['resource_name' => Product::class]
        );
    }

    private function log(string $message): void
    {
        if (!$this->shouldLog) {
            return;
        }
        $this->logger->notice($message, ['zdd_migration' => $this->getName()]);
    }

    private function isColumnNullable(string $tableName, string $columnName): bool
    {
        $schema = $this->connection->getDatabase();
        $sql = <<<SQL
            SELECT IS_NULLABLE 
            FROM information_schema.columns 
            WHERE table_schema=:schema 
              AND table_name=:tableName
              AND column_name=:columnName;
        SQL;

        $result = $this->connection->fetchOne($sql, [
            'schema' => $schema,
            'tableName' => $tableName,
            'columnName' => $columnName
        ]);

        return $result === 'YES';
    }
}
