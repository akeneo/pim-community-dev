<?php

namespace Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuid;

use Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuid\Utils\StatusAwareTrait;
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MigrateToUuidSetNotNullableUuidColumns implements MigrateToUuidStep
{
    /** @var string[] */
    private array $ignoredTables = [
        'pim_versioning_version',
        'pimee_workflow_published_product',
        'pim_comment_comment',
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

    use MigrateToUuidTrait;
    use StatusAwareTrait;

    public function __construct(
        private Connection $connection,
        private LoggerInterface $logger
    ) {
    }

    public function getDescription(): string
    {
        return 'Set not nullable every uuid columns (product_uuid like)';
    }

    public function getName(): string
    {
        return 'set_not_nullable_uuid_columns';
    }

    public function shouldBeExecuted(): bool
    {
        return 0 < $this->getMissingCount();
    }

    public function getMissingCount(): int
    {
        $count = 0;
        foreach ($this->getTablesToMigrate() as $tableName => $columnNames) {
            if ($this->tableExists($tableName) && $this->isColumnNullable($tableName, $columnNames[self::UUID_COLUMN_INDEX])) {
                $count++;
            }
        }

        foreach (['pim_comment_comment', 'pim_versioning_version'] as $tableName) {
            if (!$this->isColumnNullable($tableName, 'resource_id')) {
                $count++;
            }
        }

        return $count;
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

        return $result !== 'NO';
    }

    public function addMissing(Context $context): bool
    {
        $logContext = $context->logContext;
        $lockTables = $context->lockTables();

        $updatedItems = 0;
        foreach ($this->getTablesToMigrate() as $tableName => $columnNames) {
            $logContext->addContext('substep', $tableName);
            if ($this->tableExists($tableName) && $this->isColumnNullable($tableName, $columnNames[self::UUID_COLUMN_INDEX])) {
                $this->logger->notice(sprintf('Will set uuid column not nullable for %s', $tableName), $logContext->toArray());
                if (!$context->dryRun()) {
                    $this->setUuidColumnNotNullable(
                        $tableName,
                        $columnNames[self::UUID_COLUMN_INDEX],
                        $lockTables
                    );
                    $this->logger->notice('Substep done', $logContext->toArray(['updated_items_count' => $updatedItems+=1]));
                }
            }
        }

        foreach (['pim_comment_comment', 'pim_versioning_version'] as $tableName) {
            if (!$this->isColumnNullable($tableName, 'resource_id')) {
                $this->connection->executeQuery(\strtr(
                    <<<SQL
                    ALTER TABLE {table_name}
                    MODIFY resource_id varchar(24) COLLATE utf8mb4_unicode_ci DEFAULT NULL{algorithmInplace};
                    SQL,
                    [
                        '{table_name}' => $tableName,
                        '{algorithmInplace}' => $lockTables ? '' : ', ALGORITHM=INPLACE, LOCK=NONE',
                    ]
                ));
                $this->logger->notice('Substep done', $logContext->toArray([
                    'updated_items_count' => $updatedItems+=1,
                    'substep' => $tableName,
                ]));
            }
        }

        return true;
    }

    private function setUuidColumnNotNullable(string $tableName, string $uuidColumnName, bool $lockTables): void
    {
        $sql = <<<SQL
            ALTER TABLE `{table_name}`
            MODIFY {uuid_column_name} BINARY(16) NOT NULL {comment} {algorithmInplace};
        SQL;

        $query = \strtr(
            $sql,
            [
                '{table_name}' => $tableName,
                '{uuid_column_name}' => $uuidColumnName,
                '{algorithmInplace}' => $lockTables ? '' : ', ALGORITHM=INPLACE, LOCK=NONE',
                '{comment}' => in_array($tableName, self::TABLE_WITHOUT_UUID_COMMENT) ? '' : 'COMMENT "(DC2Type:uuid_binary)"'
            ]
        );

        $this->connection->executeQuery($query);
    }

    private function getTablesToMigrate(): array
    {
        return \array_filter(
            self::TABLES,
            fn (string $tableName): bool => !\in_array($tableName, $this->ignoredTables) && $this->tableExists($tableName),
            ARRAY_FILTER_USE_KEY
        );
    }
}
