<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuid;

use Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuid\Utils\StatusAwareTrait;
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class MigrateToUuidAddTriggers implements MigrateToUuidStep
{
    use MigrateToUuidTrait;
    use StatusAwareTrait;

    public function __construct(private Connection $connection, private LoggerInterface $logger)
    {
    }

    public function getName(): string
    {
        return 'add_triggers';
    }

    public function getDescription(): string
    {
        return 'Add triggers on foreign uuid column.';
    }

    public function shouldBeExecuted(): bool
    {
        return 0 < $this->getMissingCount();
    }

    public function getMissingCount(): int
    {
        $count = 0;
        foreach ($this->getTablesToMigrate() as $tableName => $columnNames) {
            if (!$this->triggerExists($this->getInsertTriggerName($tableName))) {
                $count++;
            }

            if (!$this->triggerExists($this->getUpdateTriggerName($tableName))) {
                $count++;
            }
        }

        return $count;
    }

    public function addMissing(Context $context): bool
    {
        $logContext = $context->logContext;
        $templateSql = <<<SQL
        CREATE TRIGGER {trigger_name}
        BEFORE {action} ON {table_name}
        FOR EACH ROW BEGIN
            IF NEW.{uuid_column_name} IS NULL THEN
                SET NEW.{uuid_column_name} = (
                    SELECT p.uuid
                    FROM pim_catalog_product p
                    WHERE p.uuid IS NOT NULL AND p.id = NEW.{id_column_name}
                );
            ELSEIF NEW.{id_column_name} IS NULL THEN
                SET NEW.{id_column_name} = (
                    SELECT p.id
                    FROM pim_catalog_product p
                    WHERE p.id IS NOT NULL AND p.uuid = NEW.{uuid_column_name}
                );
            END IF;
        END
        SQL;

        foreach ($this->getTablesToMigrate() as $tableName => $columnNames) {
            $logContext->addContext('substep', $tableName);
            $insertTriggerName = $this->getInsertTriggerName($tableName);
            if (!$this->triggerExists($insertTriggerName)) {
                $this->logger->notice(\sprintf('Will add %s trigger on "%s" table', $insertTriggerName, $tableName), $logContext->toArray());
                if (!$context->dryRun()) {
                    $this->connection->executeQuery(\strtr($templateSql, [
                        '{trigger_name}' => $insertTriggerName,
                        '{action}' => 'INSERT',
                        '{table_name}' => $tableName,
                        '{uuid_column_name}' => $columnNames[self::UUID_COLUMN_INDEX],
                        '{id_column_name}' => $columnNames[self::ID_COLUMN_INDEX],
                    ]));
                    $this->logger->notice(\sprintf('Add %s trigger on "%s" table done', $insertTriggerName, $tableName), $logContext->toArray());
                }
            }

            $updateTriggerName = $this->getUpdateTriggerName($tableName);
            if (!$this->triggerExists($updateTriggerName)) {
                $this->logger->notice(\sprintf('Will add %s trigger on "%s" table', $updateTriggerName, $tableName), $logContext->toArray());
                if (!$context->dryRun()) {
                    $this->connection->executeQuery(\strtr($templateSql, [
                        '{trigger_name}' => $updateTriggerName,
                        '{action}' => 'UPDATE',
                        '{table_name}' => $tableName,
                        '{uuid_column_name}' => $columnNames[self::UUID_COLUMN_INDEX],
                        '{id_column_name}' => $columnNames[self::ID_COLUMN_INDEX],
                    ]));
                    $this->logger->notice(\sprintf('Add %s trigger on "%s" table done', $updateTriggerName, $tableName), $logContext->toArray());
                }
            }
        }

        return true;
    }

    private function getTablesToMigrate(): array
    {
        return \array_filter(
            self::TABLES,
            fn (string $tableName): bool => 'pim_catalog_product' !== $tableName && $this->tableExists($tableName),
            ARRAY_FILTER_USE_KEY
        );
    }

    public static function getInsertTriggerName(string $tableName): string
    {
        // Some tables are too long, so we shorten them (trigger names are limited to 64 characters)
        $trigger_prefix = \strtr($tableName, [
            'data_quality_insights' => 'dqi',
            'teamwork_assistant' => 'twa',
        ]);

        return $trigger_prefix . '_uuid_insert';
    }

    public static function getUpdateTriggerName(string $tableName): string
    {
        // Some tables are too long, so we shorten them (trigger names are limited to 64 characters)
        $trigger_prefix = \strtr($tableName, [
            'data_quality_insights' => 'dqi',
            'teamwork_assistant' => 'twa',
        ]);

        return $trigger_prefix . '_uuid_update';
    }
}
