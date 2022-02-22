<?php

namespace Akeneo\Pim\Enrichment\Bundle\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Output\OutputInterface;

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

    /**
     * {@inheritDoc}
     */
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

    public function addMissing(bool $dryRun, OutputInterface $output): void
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
    }

    private function addUuidColumn(string $tableName, string $uuidColumName, string $idColumnName): void
    {
        $addUuidColumnSql = <<<SQL
            ALTER TABLE `%s`
            ADD `%s` BINARY(16) DEFAULT NULL AFTER `%s`,
            LOCK=NONE,
            ALGORITHM=INPLACE;
        SQL;

        $addUuidColumnQuery = sprintf(
            $addUuidColumnSql,
            $tableName,
            $uuidColumName,
            $idColumnName
        );

        $this->connection->executeQuery($addUuidColumnQuery);
    }

    private function addIndexOnUuid(string $tableName, string $uuidColumnName): void
    {
        $addIndexOnUuidSql = <<<SQL
            ALTER TABLE %s 
            ADD INDEX %s (%s),
            ALGORITHM=INPLACE,
            LOCK=NONE;
        SQL;

        $addIndexColumnQuery = sprintf(
            $addIndexOnUuidSql,
            $tableName,
            self::INDEX_NAME,
            $uuidColumnName
        );

        $this->connection->executeQuery($addIndexColumnQuery);
    }
}
