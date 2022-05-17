<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Command\ZddMigrations;

use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class MigrateZddCommand extends Command
{
    protected static $defaultName = 'pim:zdd-migration:migrate';

    /** @var ZddMigration[] */
    private array $zddMigrations;

    public function __construct(
        private Connection $connection,
        private LoggerInterface $logger,
        \Traversable $zddMigrations
    ) {
        $this->zddMigrations = iterator_to_array($zddMigrations);

        Assert::allIsInstanceOf($this->zddMigrations, ZddMigration::class);
        usort($this->zddMigrations, fn ($a, $b) => \strcmp(
            (new \ReflectionClass($a))->getShortName(),
            (new \ReflectionClass($b))->getShortName()
        ));

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Execute ZDD Migrations');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->logger->notice('pim:zdd-migration:migrate start');

        $migrationCount = 0;
        /** @var ZddMigration $zddMigration */
        foreach($this->zddMigrations as $zddMigration) {
            if (!$this->isMigrated($zddMigration)) {
                try {
                    $this->logger->notice(
                        sprintf('pim:zdd-migration:migrate migrate %s', $zddMigration->getName()),
                        [
                            'name' => $zddMigration->getName(),
                        ]
                    );
                    $startMigrationTime = \time();
                    $zddMigration->migrate();
                    $this->logger->notice(
                        sprintf('pim:zdd-migration:migrate migrated %s', $zddMigration->getName()),
                        [
                            'name' => $zddMigration->getName(),
                            'migration_duration_in_second' => time() - $startMigrationTime,
                        ]
                    );
                    $this->markAsMigrated($zddMigration);
                    $migrationCount++;
                } catch (\Throwable $e) {
                    $this->logger->error(
                        sprintf('pim:zdd-migration:migrate errored %s', $zddMigration->getName()),
                        [
                            'name' => $zddMigration->getName(),
                            'exception' => $e,
                        ]
                    );

                    $output->write($e->getMessage());
                    return Command::FAILURE;
                }
            }
        }

        $this->logger->notice('pim:zdd-migration:migrate end', [
            'migrations_done' => $migrationCount,
        ]);

        return Command::SUCCESS;
    }

    private function isMigrated(ZddMigration $zddMigration): bool
    {
        $sql = <<<SQL
            SELECT EXISTS (
                SELECT 1
                FROM pim_one_time_task
                WHERE pim_one_time_task.code=:code
                  AND pim_one_time_task.status=:status
                LIMIT 1
            ) AS missing
        SQL;

        return (bool) $this->connection->fetchOne($sql, [
            'code' => $this->getZddMigrationCode($zddMigration),
            'status' => 'finished',
        ]);
    }

    private function markAsMigrated(ZddMigration $zddMigration): void
    {
        $this->connection->executeQuery(<<<SQL
            INSERT INTO `pim_one_time_task` (`code`, `status`, `start_time`, `values`) 
            VALUES (:code, :status, NOW(), :values)
            ON DUPLICATE KEY UPDATE status=VALUES(status), start_time=NOW();
        SQL, [
            'code' => $this->getZddMigrationCode($zddMigration),
            'status' => 'finished',
            'values' => \json_encode((object) []),
        ]);
    }

    private function getZddMigrationCode(ZddMigration $zddMigration): string
    {
        return \sprintf('zdd_%s', $zddMigration->getName());
    }
}
