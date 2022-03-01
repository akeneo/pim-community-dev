<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuid;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class MigrateToUuidAddTriggers implements MigrateToUuidStep
{
    use MigrateToUuidTrait;

    public function __construct(private Connection $connection)
    {
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

    public function addMissing(bool $dryRun, OutputInterface $output): bool
    {
        $templateSql = <<<SQL
        CREATE TRIGGER {trigger_name}
        AFTER {action} ON {table_name} FOR EACH ROW
        UPDATE {table_name} t, pim_catalog_product p
        SET t.{uuid_column_name} = p.uuid
        WHERE p.id = t.{id_column_name} AND t.{uuid_column_name} IS NULL AND p.uuid IS NOT NULL;
        SQL;

        foreach ($this->getTablesToMigrate() as $tableName => $columnNames) {
            $insertTriggerName = $this->getInsertTriggerName($tableName);
            if (!$this->triggerExists($insertTriggerName)) {
                $output->writeln(sprintf('    Will add %s trigger on "%s" table', $insertTriggerName, $tableName));
                if (!$dryRun) {
                    $insertTriggerSql = strtr($templateSql, [
                        '{trigger_name}' => $insertTriggerName,
                        '{action}' => 'INSERT',
                        '{table_name}' => $tableName,
                        '{uuid_column_name}' => $columnNames[self::UUID_COLUMN_INDEX],
                        '{id_column_name}' => $columnNames[self::ID_COLUMN_INDEX],
                    ]);
                    $this->connection->executeQuery($insertTriggerSql);
                }
            }

            $updateTriggerName = $this->getUpdateTriggerName($tableName);
            if (!$this->triggerExists($insertTriggerName)) {
                $output->writeln(sprintf('    Will add %s trigger on "%s" table', $updateTriggerName, $tableName));
                if (!$dryRun) {
                    $updateTriggerSql = strtr($templateSql, [
                        '{trigger_name}' => $updateTriggerName,
                        '{action}' => 'UPDATE',
                        '{table_name}' => $tableName,
                        '{uuid_column_name}' => $columnNames[self::UUID_COLUMN_INDEX],
                        '{id_column_name}' => $columnNames[self::ID_COLUMN_INDEX],
                    ]);
                    $this->connection->executeQuery($updateTriggerSql);
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

    private function getInsertTriggerName(string $tableName): string
    {
        // DQI tables are too long (trigger names are limited to 64 characters)
        return \str_replace('data_quality_insights', 'dqi', $tableName) . '_uuid_insert';
    }

    private function getUpdateTriggerName(string $tableName): string
    {
        // DQI tables are too long (trigger names are limited to 64 characters)
        return \str_replace('data_quality_insights', 'dqi', $tableName) . '_uuid_update';
    }
}
