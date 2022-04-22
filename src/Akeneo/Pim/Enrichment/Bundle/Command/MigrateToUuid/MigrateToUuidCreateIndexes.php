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
            $indexName = $tableProperties[self::UUID_COLUMN_INDEX_NAME_INDEX];
            if (null !== $indexName && $this->tableExists($tableName) && !$this->indexExists($tableName, $indexName)) {
                $count++;
            }
        }

        return $count;
    }

    public function addMissing(Context $context): bool
    {
        $logContext = $context->logContext;

        $updatedItems = 0;
        foreach (MigrateToUuidStep::TABLES as $tableName => $tableProperties) {
            $logContext->addContext('substep', $tableName);
            $indexName = $tableProperties[self::UUID_COLUMN_INDEX_NAME_INDEX];
            if (null !== $indexName && $this->tableExists($tableName) && !$this->indexExists($tableName, $indexName)) {
                $this->logger->notice(sprintf('Will add index for %s', $tableName), $logContext->toArray());
                if (!$context->dryRun()) {
                    $this->addIndexOnUuid(
                        $tableName,
                        $tableProperties[self::UUID_COLUMN_INDEX],
                        $indexName
                    );
                    $this->logger->notice(
                        \sprintf('Substep done: indexes added for %s', $tableName),
                        $logContext->toArray(['updated_items_count' => ++$updatedItems])
                    );
                }
            }
        }

        return true;
    }

    private function addIndexOnUuid(string $tableName, string $uuidColumName, string $indexName): void
    {
        $addUuidColumnAndIndexOnUuidSql = <<<SQL
            ALTER TABLE `{table_name}` ADD {unique} INDEX `{index_name}` (`{uuid_column_name}`),
                ALGORITHM=INPLACE,
                LOCK=NONE;
        SQL;

        $addUuidColumnAndIndexOnUuidQuery = \strtr(
            $addUuidColumnAndIndexOnUuidSql,
            [
                '{unique}' => $tableName === 'pimee_workflow_published_product' ? 'UNIQUE' : '',
                '{table_name}' => $tableName,
                '{uuid_column_name}' => $uuidColumName,
                '{index_name}' => $indexName,
            ]
        );

        $this->connection->executeQuery($addUuidColumnAndIndexOnUuidQuery);

        if (\in_array($tableName, ['pim_versioning_version', 'pim_comment_comment']) &&
            !$this->indexExists($tableName, 'migrate_to_uuid_temp_index_to_delete')
        ) {
            $this->connection->executeQuery(\strtr(
                <<<SQL
                ALTER TABLE `{tableName}`
                ADD INDEX `migrate_to_uuid_temp_index_to_delete` (`resource_name`, `resource_uuid`, `resource_id`),
                ALGORITHM=INPLACE,
                LOCK=NONE;
                SQL,
                ['{tableName}' => $tableName]
            ));
        }
    }
}
