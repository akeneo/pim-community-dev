<?php

namespace Akeneo\Pim\Enrichment\Bundle\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateToUuidCreateColumns implements MigrateToUuidStep
{
    use MigrateToUuidTrait;

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
            if ($this->tableExists($tableName) && !$this->columnExists($tableName, $columnNames[1])) {
                $count++;
            }
        }

        return $count;
    }

    public function addMissing(bool $dryRun, OutputInterface $output): void
    {
        foreach (MigrateToUuidStep::TABLES as $tableName => $columnNames) {
            if ($this->tableExists($tableName) && !$this->columnExists($tableName, $columnNames[1])) {
                $output->writeln(sprintf('    Will add %s', $tableName));
                if (!$dryRun) {
                    $addUuidColumnQuery = sprintf(<<<SQL
        ALTER TABLE `%s` ADD `%s` BINARY(16) DEFAULT NULL AFTER `%s`, LOCK=NONE, ALGORITHM=INPLACE;
        SQL, $tableName, $columnNames[1], $columnNames[0]);
                    $this->connection->executeQuery($addUuidColumnQuery);

                    $indexName = 'product_uuid';
                    $addIndexColumnQuery = sprintf(<<<SQL
        ALTER TABLE %s ADD INDEX %s (%s), ALGORITHM=INPLACE, LOCK=NONE;
        SQL, $tableName, $indexName, $columnNames[1]);
                    $this->connection->executeQuery($addIndexColumnQuery);
                }
            }
        }
    }
}
