<?php

namespace Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuid;

use Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuid\Utils\StatusAwareTrait;
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MigrateToUuidCreateIndexes implements MigrateToUuidStep
{
    use MigrateToUuidTrait;
    use StatusAwareTrait;

    private const DEFAULT_INDEX_NAME = 'product_uuid';

    public function __construct(
        private Connection $connection,
        private LoggerInterface $logger
    ) {
    }

    public function getDescription(): string
    {
        return 'Add indexes on uuid columns for pim_catalog_product table and every foreign table';
    }

    public function getName(): string
    {
        return 'create_uuid_indexes';
    }

    public function shouldBeExecuted(): bool
    {
        return 0 < $this->getMissingCount();
    }

    public function getMissingCount(): int
    {
        $count = 0;
        foreach (MigrateToUuidStep::TABLES as $tableName => $tableProperties) {
            if (!$this->tableExists($tableName)) {
                continue;
            }
            $indexName = $tableProperties[self::UUID_COLUMN_INDEX_NAME_INDEX];
            if (null !== $indexName && !$this->indexExists($tableName, $indexName)) {
                $count++;
            }
            $additionalIndexes = $tableProperties[self::TEMPORARY_INDEXES_INDEX] ?? [];
            foreach ($additionalIndexes as $indexName => $columns) {
                if (!$this->indexExists($tableName, $indexName)) {
                    $count++;
                }
            }
        }

        return $count;
    }

    public function addMissing(Context $context): bool
    {
        $logContext = $context->logContext;
        $lockTables = $context->lockTables();

        $updatedItems = 0;
        foreach (MigrateToUuidStep::TABLES as $tableName => $tableProperties) {
            $logContext->addContext('substep', $tableName);
            $indexName = $tableProperties[self::UUID_COLUMN_INDEX_NAME_INDEX];
            if (!$this->tableExists($tableName)) {
                continue;
            }

            if (null !== $indexName && !$this->indexExists($tableName, $indexName)) {
                $this->logger->notice(sprintf('Will add index on uuid for %s', $tableName), $logContext->toArray());
                if (!$context->dryRun()) {
                    $this->addIndexOnUuid(
                        $tableName,
                        $tableProperties[self::UUID_COLUMN_INDEX],
                        $indexName,
                        $lockTables
                    );
                    $this->logger->notice(
                        \sprintf('index on uuid added for %s', $tableName),
                        $logContext->toArray(['updated_items_count' => ++$updatedItems])
                    );
                }
            }
            $additionalIndexes = $tableProperties[self::TEMPORARY_INDEXES_INDEX] ?? [];
            foreach ($additionalIndexes as $additionalIndexName => $columns) {
                if ($this->tableExists($tableName) && !$this->indexExists($tableName, $additionalIndexName)) {
                    $this->logger->notice(sprintf('Will add additional index for %s', $tableName), $logContext->toArray());
                    if (!$context->dryRun()) {
                        $this->addAdditionalIndex(
                            $tableName,
                            $additionalIndexName,
                            $columns,
                            $lockTables
                        );
                        $this->logger->notice(
                            \sprintf('additional indexes added for %s', $tableName),
                            $logContext->toArray(['updated_items_count' => ++$updatedItems])
                        );
                    }
                }
            }
            $this->logger->notice(
                \sprintf('Substep done: indexes added for %s', $tableName),
                $logContext->toArray()
            );
        }

        return true;
    }

    private function addIndexOnUuid(
        string $tableName,
        string $uuidColumName,
        string $indexName,
        bool $lockTables
    ): void {
        $addUuidColumnAndIndexOnUuidSql = <<<SQL
            ALTER TABLE `{table_name}` ADD {unique} INDEX `{index_name}` (`{uuid_column_name}`){algorithmInplace};
        SQL;

        $addUuidColumnAndIndexOnUuidQuery = \strtr(
            $addUuidColumnAndIndexOnUuidSql,
            [
                '{unique}' => $tableName === 'pimee_workflow_published_product' ? 'UNIQUE' : '',
                '{table_name}' => $tableName,
                '{uuid_column_name}' => $uuidColumName,
                '{index_name}' => $indexName,
                '{algorithmInplace}' => $lockTables ? '' : ', ALGORITHM=INPLACE, LOCK=NONE',
            ]
        );

        $this->connection->executeQuery($addUuidColumnAndIndexOnUuidQuery);
    }

    /**
     * @param string[] $columns
     */
    private function addAdditionalIndex(
        string $tableName,
        string $indexName,
        array $columns,
        bool $lockTables
    ): void {
        $this->connection->executeQuery(\strtr(
            <<<SQL
            ALTER TABLE `{tableName}`
            ADD INDEX `{indexName}` (`{columnNames}`){algorithmInplace};
            SQL,
            [
                '{tableName}' => $tableName,
                '{indexName}' => $indexName,
                '{columnNames}' => \implode('`, `', $columns),
                '{algorithmInplace}' => $lockTables ? '' : ', ALGORITHM=INPLACE, LOCK=NONE',
            ]
        ));
    }
}
