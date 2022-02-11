<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Command;

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
        foreach (self::TABLES as $tableName => $columnNames) {
            if ('pim_catalog_product' === $tableName || !$this->tableExists($tableName)) {
                continue;
            }

            if (!$this->triggerExists($this->getInsertTrigger($tableName))) {
                $count++;
            }

            if (!$this->triggerExists($this->getUpdateTrigger($tableName))) {
                $count++;
            }
        }

        return $count;
    }

    public function addMissing(bool $dryRun, OutputInterface $output): void
    {
        /** @todo Fix the privilege issues :sad_dog: */

        $templateSql = <<<SQL
CREATE TRIGGER {trigger_name} /*+ SET_VAR( log_bin_trust_function_creators = 1) */
    AFTER {action} ON {table_name} FOR EACH ROW
    UPDATE {table_name} t, pim_catalog_product p SET t.{uuid_column_name} = p.uuid
    WHERE p.id = t.{id_column_name} AND p.uuid IS NOT NULL;
SQL;

        foreach (self::TABLES as $tableName => $columnNames) {
            if ('pim_catalog_product' === $tableName || !$this->tableExists($tableName)) {
                continue;
            }

            $insertTriggerName = $this->getInsertTrigger($tableName);
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
                    $this->connection->executeQuery('set global log_bin_trust_function_creators=1;');
                    $this->connection->executeQuery($insertTriggerSql);
                }
            }

            $updateTriggerName = $this->getUpdateTrigger($tableName);
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
                    $this->connection->executeQuery('set global log_bin_trust_function_creators=1;');
                    $this->connection->executeQuery($updateTriggerSql);
                }
            }
        }
    }

    private function getInsertTrigger(string $tableName): string
    {
        return $tableName . '_trigger_uuid_insert';
    }

    private function getUpdateTrigger(string $tableName): string
    {
        return $tableName . '_trigger_uuid_update';
    }
}
