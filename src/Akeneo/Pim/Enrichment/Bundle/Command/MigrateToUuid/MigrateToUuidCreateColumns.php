<?php

namespace Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuid;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MigrateToUuidCreateColumns implements MigrateToUuidStep
{
    use MigrateToUuidTrait;

    private const INDEX_NAME = 'product_uuid';

    public function __construct(private Connection $connection)
    {
    }

    public function getDescription(): string
    {
        return 'Add uuid columns for pim_catalog_product table and every foreign tables';
    }

    public function shouldBeExecuted(): bool
    {
        return 0 < $this->getMissingCount();
    }

    public function getMissingCount(): int
    {
        $count = 0;
        foreach (MigrateToUuidStep::TABLES as $tableName => $columnNames) {
            if ($this->tableExists($tableName) && !$this->columnExists($tableName, $columnNames[self::UUID_COLUMN_INDEX])) {
                $count++;
            }
        }

        return $count;
    }

    public function addMissing(bool $dryRun, OutputInterface $output): bool
    {
        foreach (MigrateToUuidStep::TABLES as $tableName => $columnNames) {
            if ($this->tableExists($tableName) && !$this->columnExists($tableName, $columnNames[self::UUID_COLUMN_INDEX])) {
                $output->writeln(sprintf('    Will add %s', $tableName));
                if (!$dryRun) {
                    $this->addUuidColumn(
                        $tableName,
                        $columnNames[self::UUID_COLUMN_INDEX],
                        $columnNames[self::ID_COLUMN_INDEX]
                    );

                    $this->addIndexOnUuid(
                        $tableName,
                        $columnNames[self::UUID_COLUMN_INDEX]
                    );
                }
            }
        }

        return true;
    }

    private function addUuidColumn(string $tableName, string $uuidColumName, string $idColumnName): void
    {
        $addUuidColumnSql = <<<SQL
            ALTER TABLE `{table_name}`
            ADD `{uuid_column_name}` BINARY(16) DEFAULT NULL AFTER `{id_column_name}`,
            LOCK=NONE,
            ALGORITHM=INPLACE;
        SQL;

        $addUuidColumnQuery = \strtr(
            $addUuidColumnSql,
            [
                '{table_name}' => $tableName,
                '{uuid_column_name}' => $uuidColumName,
                '{id_column_name}' => $idColumnName,
            ]
        );

        $this->connection->executeQuery($addUuidColumnQuery);
    }

    private function addIndexOnUuid(string $tableName, string $uuidColumnName): void
    {
        $addIndexOnUuidSql = <<<SQL
            ALTER TABLE {table_name}
            ADD INDEX {index_name} ({uuid_column_name}),
            ALGORITHM=INPLACE,
            LOCK=NONE;
        SQL;

        $addIndexColumnQuery = \strtr(
            $addIndexOnUuidSql,
            [
                '{table_name}' => $tableName,
                '{index_name}' => self::INDEX_NAME,
                '{uuid_column_name}' => $uuidColumnName,
            ]
        );

        $this->connection->executeQuery($addIndexColumnQuery);
    }
}
