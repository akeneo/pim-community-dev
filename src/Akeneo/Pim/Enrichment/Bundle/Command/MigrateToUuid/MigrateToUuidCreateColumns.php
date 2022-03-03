<?php

namespace Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuid;

use Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuid\Utils\StackedContextProcessor;
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MigrateToUuidCreateColumns implements MigrateToUuidStep
{
    use MigrateToUuidTrait;

    private const INDEX_NAME = 'product_uuid';

    public function __construct(
        private Connection $connection,
        private LoggerInterface $logger,
        private StackedContextProcessor $contextProcessor
    ) {
    }

    public function getDescription(): string
    {
        return 'Add uuid columns for pim_catalog_product table and every foreign tables';
    }

    public function getName(): string
    {
        return 'create_uuid_columns';
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

    public function addMissing(Context $context, OutputInterface $output): bool
    {
        $updatedItems = 0;
        foreach (MigrateToUuidStep::TABLES as $tableName => $columnNames) {
            $this->contextProcessor->push(['substep' => $tableName]);
            if ($this->tableExists($tableName) && !$this->columnExists($tableName, $columnNames[self::UUID_COLUMN_INDEX])) {
                $this->logger->notice(sprintf('Will add %s', $tableName));
                if (!$context->dryRun()) {
                    $stepStartTime = \microtime(true);
                    $this->addUuidColumnAndIndexOnUuid(
                        $tableName,
                        $columnNames[self::UUID_COLUMN_INDEX],
                        $columnNames[self::ID_COLUMN_INDEX]
                    );
                    $stepDuration = \microtime(true) - $stepStartTime;
                    $this->logger->notice(\sprintf('Done in %0.2f seconds', $stepDuration), ['updated_items_count' => $updatedItems+=1]);
                }
            }
            $this->contextProcessor->pop(); //pop sustep name
        }

        return true;
    }

    private function addUuidColumnAndIndexOnUuid(string $tableName, string $uuidColumName, string $idColumnName): void
    {
        $addUuidColumnAndIndexOnUuidSql = <<<SQL
            ALTER TABLE `{table_name}`
                ADD `{uuid_column_name}` BINARY(16) DEFAULT NULL AFTER `{id_column_name}`,
                    ALGORITHM=INPLACE,
                    LOCK=NONE,
                ADD INDEX `{index_name}` (`{uuid_column_name}`),
                    ALGORITHM=INPLACE,
                    LOCK=NONE;
        SQL;

        $addUuidColumnAndIndexOnUuidQuery = \strtr(
            $addUuidColumnAndIndexOnUuidSql,
            [
                '{table_name}' => $tableName,
                '{uuid_column_name}' => $uuidColumName,
                '{id_column_name}' => $idColumnName,
                '{index_name}' => self::INDEX_NAME,
            ]
        );

        $this->connection->executeQuery($addUuidColumnAndIndexOnUuidQuery);
    }
}
