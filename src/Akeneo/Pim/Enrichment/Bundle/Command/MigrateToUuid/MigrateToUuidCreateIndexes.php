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
    private const INDEX_NAMES = [
        'pim_catalog_association' => 'IDX_CC27100147D93336',
        'pim_catalog_association_product' => 'IDX_3A3A49D45C977207',
        'pim_catalog_association_product_model_to_product' => 'IDX_3FF3ED195C977207',
        'pim_catalog_category_product' => 'IDX_512179C15C977207',
        'pim_catalog_group_product' => 'IDX_7AC0C83A5C977207',
        'pim_catalog_product_unique_data' => 'IDX_E0768BA35C977207',
        'pimee_workflow_product_draft' => 'IDX_6F63A25F5C977207',
        'pimee_workflow_published_product' => 'UNIQ_E3566E6987DBB431',
    ];

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
        foreach (MigrateToUuidStep::TABLES as $tableName => $columnNames) {
            if ($this->tableExists($tableName) && !$this->indexExists($tableName, $columnNames[self::UUID_COLUMN_INDEX])) {
                $count++;
            }
        }

        return $count;
    }

    public function addMissing(Context $context): bool
    {
        $logContext = $context->logContext;

        $updatedItems = 0;
        foreach (MigrateToUuidStep::TABLES as $tableName => $columnNames) {
            $logContext->addContext('substep', $tableName);
            if ($this->tableExists($tableName) && !$this->indexExists($tableName, $columnNames[self::UUID_COLUMN_INDEX])) {
                $this->logger->notice(sprintf('Will add %s', $tableName), $logContext->toArray());
                if (!$context->dryRun()) {
                    $this->addIndexOnUuid(
                        $tableName,
                        $columnNames[self::UUID_COLUMN_INDEX]
                    );
                    $this->logger->notice(
                        'Substep done',
                        $logContext->toArray(['updated_items_count' => $updatedItems += 1])
                    );
                }
            }
        }

        return true;
    }

    private function addIndexOnUuid(string $tableName, string $uuidColumName): void
    {
        $addUuidColumnAndIndexOnUuidSql = <<<SQL
            ALTER TABLE `{table_name}`
                ADD {UNIQUE} INDEX `{index_name}` (`{uuid_column_name}`),
                    ALGORITHM=INPLACE,
                    LOCK=NONE;
        SQL;
        if (!\in_array($tableName, [
                'pim_data_quality_insights_product_criteria_evaluation',
                'pim_data_quality_insights_product_score',
                'pim_versioning_version',
            ]
        )) {
            $addUuidColumnAndIndexOnUuidQuery = \strtr(
                $addUuidColumnAndIndexOnUuidSql,
                [
                    '{unique}' => $tableName === 'pimee_workflow_published_product' ? 'UNIQUE' : '',
                    '{table_name}' => $tableName,
                    '{uuid_column_name}' => $uuidColumName,
                    '{index_name}' => self::INDEX_NAMES[$tableName] ?? self::DEFAULT_INDEX_NAME,
                ]
            );

            $this->connection->executeQuery($addUuidColumnAndIndexOnUuidQuery);
        }

        if ('pim_versioning_version' === $tableName) {
            // TODO CPM-581: remove this index once the pim_versioning_version is fully migrated
            $this->connection->executeQuery(
                <<<SQL
                ALTER TABLE `pim_versioning_version`
                ADD INDEX `migrate_to_uuid_temp_index_to_delete` (`resource_name`, `resource_uuid`, `resource_id`),
                ALGORITHM=INPLACE,
                LOCK=NONE;
                SQL
            );
        }
    }
}
