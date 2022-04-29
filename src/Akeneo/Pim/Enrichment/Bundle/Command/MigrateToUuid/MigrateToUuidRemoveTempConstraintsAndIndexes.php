<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuid;

use Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuid\Utils\StatusAwareTrait;
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class MigrateToUuidRemoveTempConstraintsAndIndexes implements MigrateToUuidStep
{
    use MigrateToUuidTrait;
    use StatusAwareTrait;

    public function __construct(private Connection $connection, private LoggerInterface $logger)
    {
    }

    public function getDescription(): string
    {
        return 'Remove temporary constraints and indexes created for migration performance';
    }

    public function getName(): string
    {
        return 'remove_temporary_constraints_and_indexes';
    }

    public function shouldBeExecuted(): bool
    {
        return 0 < $this->getMissingCount();
    }

    public function getMissingCount(): int
    {
        $count = 0;
        foreach (['pim_versioning_version', 'pim_comment_comment'] as $tableName) {
            if ($this->tableExists($tableName)) {
                if ($this->indexExists($tableName, 'migrate_to_uuid_temp_index_to_delete')) {
                    $count++;
                }
                if($this->constraintExists($tableName, 'migrate_to_uuid_temp_index_to_delete')) {
                    $count++;
                }
            }
        }

        return $count;
    }

    public function addMissing(Context $context): bool
    {
        $logContext = $context->logContext;
        $updatedItems = 0;

        foreach (['pim_versioning_version', 'pim_comment_comment'] as $tableName) {
            if ($this->tableExists($tableName)) {
                $logContext->addContext('substep', 'remove_index_' . $tableName);
                if ($this->indexExists($tableName, 'migrate_to_uuid_temp_index_to_delete')) {
                    $this->logger->notice(sprintf('Will remove %s index %s', $tableName, 'migrate_to_uuid_temp_index_to_delete'), $logContext->toArray());
                    if (!$context->dryRun()) {
                        $this->removeIndex($tableName, 'migrate_to_uuid_temp_index_to_delete');
                        $this->logger->notice('Substep done', $logContext->toArray(['updated_items_count' => $updatedItems+=1]));
                    }
                }

                $logContext->addContext('substep', 'remove_constraint_' . $tableName);
                if ($this->constraintExists($tableName, 'migrate_to_uuid_temp_index_to_delete')) {
                    $this->logger->notice(sprintf('Will remove %s constraint %s', $tableName, 'migrate_to_uuid_temp_index_to_delete'), $logContext->toArray());
                    if (!$context->dryRun()) {
                        $this->removeConstraint($tableName, 'migrate_to_uuid_temp_index_to_delete');
                        $this->logger->notice('Substep done', $logContext->toArray(['updated_items_count' => $updatedItems+=1]));
                    }
                }
            }
        }

        return true;
    }

    private function removeConstraint(string $tableName, string $constraintName, array $columnNames): void
    {
        $sql = <<<SQL
            ALTER TABLE {tableName} DROP CONSTRAINT {constraintName}
        SQL;

        $query = \strtr($sql, [
            '{tableName}' => $tableName,
            '{constraintName}' => $constraintName,
        ]);

        $this->connection->executeQuery($query);
    }

    private function removeIndex(string $tableName, string $indexName): void
    {
        $sql = <<<SQL
            ALTER TABLE {tableName}
            DROP INDEX {indexName}
        SQL;

        $query = \strtr($sql, [
            '{tableName}' => $tableName,
            '{indexName}' => $indexName,
        ]);

        $this->connection->executeQuery($query);
    }
}
