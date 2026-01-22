<?php

declare(strict_types=1);

namespace Akeneo\Platform\Installer\Infrastructure\Command;

use Akeneo\Platform\Installer\Infrastructure\Exception\UcsOnlyMigrationException;
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
final class MigrateZddCommand extends Command
{
    protected static $defaultName = 'pim:zdd-migration:migrate';

    /** @var ZddMigration[] */
    private array $zddMigrations;

    public function __construct(
        private readonly Connection $connection,
        private readonly LoggerInterface $logger,
        \Traversable $zddMigrations,
    ) {
        $this->zddMigrations = iterator_to_array($zddMigrations);

        Assert::allIsInstanceOf($this->zddMigrations, ZddMigration::class);
        usort($this->zddMigrations, fn ($a, $b) => \strcmp(
            (new \ReflectionClass($a))->getShortName(),
            (new \ReflectionClass($b))->getShortName(),
        ));

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Execute ZDD Migrations');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->tableExists('pim_one_time_task')) {
            $this->logger->warning(
                sprintf('%s - skip - Table pim_one_time_task does not exist', self::$defaultName),
                ['action' => 'skip'],
            );

            return Command::SUCCESS;
        }

        $this->logger->notice(sprintf('%s - start_command', self::$defaultName), [
            'action' => 'start_command',
        ]);

        $migrationCount = 0;
        /** @var ZddMigration $zddMigration */
        foreach ($this->zddMigrations as $zddMigration) {
            if (!$this->isMigrated($zddMigration)) {
                try {
                    $this->logger->notice(
                        sprintf('%s - start_migration - %s', self::$defaultName, $zddMigration->getName()),
                        [
                            'action' => 'start_migration',
                            'migration_name' => $zddMigration->getName(),
                        ],
                    );
                    $startMigrationTime = \time();
                    $zddMigration->migrate();
                    $duration = time() - $startMigrationTime;
                    $this->logger->notice(
                        sprintf('%s - end_migration - %s in %ss', self::$defaultName, $zddMigration->getName(), $duration),
                        [
                            'action' => 'end_migration',
                            'migration_name' => $zddMigration->getName(),
                            'migration_duration_in_second' => $duration,
                        ],
                    );
                    $this->markAsMigrated($zddMigration);
                    ++$migrationCount;
                } catch (UcsOnlyMigrationException $e) {
                    // @todo: Catch to remove when all flexibility clients will be migrated to the UCS platform (see JEL-359)
                    $this->logger->notice(sprintf('The migration %s will be done on UCS platform', $zddMigration->getName()));
                } catch (\Throwable $e) {
                    $this->logger->error(
                        sprintf('%s - errored_migration - %s', self::$defaultName, $zddMigration->getName()),
                        [
                            'action' => 'errored_migration',
                            'migration_name' => $zddMigration->getName(),
                            'exception' => $e,
                        ],
                    );

                    $output->write($e->getMessage());

                    return Command::FAILURE;
                }
            }
        }

        $this->logger->notice(sprintf('%s - end_command - %d migration(s) done', self::$defaultName, $migrationCount), [
            'action' => 'end_command',
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

    private function tableExists(string $tableName): bool
    {
        $rows = $this->connection->fetchAllAssociative(
            <<<SQL
                SHOW TABLES LIKE :tableName
            SQL,
            ['tableName' => $tableName],
        );

        return count($rows) >= 1;
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
            'values' => \json_encode((object) [], JSON_THROW_ON_ERROR),
        ]);
    }

    private function getZddMigrationCode(ZddMigration $zddMigration): string
    {
        return \sprintf('zdd_%s', $zddMigration->getName());
    }
}
