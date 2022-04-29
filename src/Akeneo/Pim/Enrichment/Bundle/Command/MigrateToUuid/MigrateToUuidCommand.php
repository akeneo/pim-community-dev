<?php

namespace Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuid;

use Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuid\Utils\LogContext;
use Akeneo\Platform\Job\Domain\Model\Status;
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MigrateToUuidCommand extends Command
{
    use MigrateToUuidTrait;

    protected static $defaultName = 'pim:product:migrate-to-uuid';
    private static $DQI_JOB_NAME = 'data_quality_insights_evaluations';
    private static $WAIT_TIME_IN_SECONDS = 30;

    /** @var array<MigrateToUuidStep> */
    private array $steps;

    public function __construct(
        MigrateToUuidStep $migrateToUuidCreateIndexes,
        MigrateToUuidStep $migrateToUuidAddTriggers,
        MigrateToUuidStep $migrateToUuidFillProductUuid,
        MigrateToUuidStep $migrateToUuidFillForeignUuid,
        MigrateToUuidStep $migrateToUuidFillJson,
        MigrateToUuidStep $migrateToUuidSetNotNullableUuidColumns,
        MigrateToUuidStep $migrateToUuidAddConstraints,
        MigrateToUuidStep $migrateToUuidReindexElasticsearch,
        private LoggerInterface $logger,
        private Connection $connection
    ) {
        parent::__construct();
        $this->steps = [
            $migrateToUuidCreateIndexes,
            $migrateToUuidAddTriggers,
            $migrateToUuidFillProductUuid,
            $migrateToUuidFillForeignUuid,
            $migrateToUuidFillJson,
            $migrateToUuidSetNotNullableUuidColumns,
            $migrateToUuidAddConstraints,
            $migrateToUuidReindexElasticsearch,
        ];
    }

    protected function configure()
    {
        $this->setDescription('Migrate databases to product uuids');
        $this->addOption('dry-run', 'd', InputOption::VALUE_NEGATABLE, 'dry run', false);
        $this->addOption('with-stats', 's', InputOption::VALUE_NEGATABLE, 'Display stats (be careful the command is way too slow)', false);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->columnExists('pim_catalog_association', 'owner_id')) {
            $output->writeln('Migration cannot be ran on a fresh install');

            return self::SUCCESS;
        }

        $withStats = $input->getOption('with-stats');
        $context = new Context($input->getOption('dry-run'), $withStats);

        if (!$this->shouldBeExecuted()) {
            $this->logger->notice('No step should be executed. Skip the migration.');

            return self::SUCCESS;
        }

        while ($this->hasDQIJobStarted()) {
            $this->logger->notice(sprintf(
                'There is a "%s" job in progress. Wait for %d secondes before retry migration start...',
                self::$DQI_JOB_NAME,
                self::$WAIT_TIME_IN_SECONDS
            ));

            sleep(self::$WAIT_TIME_IN_SECONDS);
        }

        $this->start();
        $startMigrationTime = \time();
        $this->logger->notice('Migration start');

        try {
            foreach ($this->steps as $step) {
                $logContext = new LogContext($step);
                $context->logContext = $logContext;

                if ($withStats) {
                    $missingCount = $step->getMissingCount();
                    $logContext->addContext('total_missing_items_count', $missingCount);
                    $this->logger->notice('Missing items', $logContext->toArray());
                } else {
                    $logContext->addContext('total_missing_items_count', null);
                }

                $step->setStatusInProgress();
                $this->logger->notice(\sprintf('Starting step %s', $step->getName()), $logContext->toArray());
                if (!$step->addMissing($context)) {
                    $step->setStatusInError();
                    $this->logger->error('An item can not be migrated. Step stopped.', $logContext->toArray());
                    $this->logger->notice('Migration stopped', ['migration_duration_in_second' => time() - $startMigrationTime]);
                    return Command::FAILURE;
                }
                $step->setStatusDone();
                $this->logger->notice(
                    \sprintf('Step done in %0.2f seconds (%s)', $step->getDuration(), $step->getName()),
                    $logContext->toArray(['migration_duration_in_second' => time() - $startMigrationTime])
                );
            }

            $this->logger->notice('Migration done!', ['migration_duration_in_second' => time() - $startMigrationTime]);
        } finally {
            $this->stop();
        }

        return Command::SUCCESS;
    }

    private function start()
    {
        $this->connection->executeQuery(<<<SQL
            INSERT INTO `pim_one_time_task` (`code`, `status`, `start_time`, `values`) 
            VALUES (:code, :status, NOW(), :values)
            ON DUPLICATE KEY UPDATE status='started', start_time=NOW();
        SQL, [
            'code' => self::$defaultName,
            'status' => 'started',
            'values' => \json_encode((object) []),
        ]);
    }

    private function stop()
    {
        $this->connection->executeQuery(<<<SQL
            DELETE FROM `pim_one_time_task` WHERE code=:code
        SQL, [
            'code' => self::$defaultName
        ]);
    }

    private function hasDQIJobStarted(): bool
    {
        $sql = <<<SQL
            SELECT EXISTS (
                SELECT 1
                FROM akeneo_batch_job_execution abje
                    INNER JOIN akeneo_batch_job_instance abji 
                        ON abje.job_instance_id = abji.id
                WHERE abji.code=:code
                  AND abje.status=:status
                LIMIT 1
            ) AS missing
        SQL;

        return (bool) $this->connection->fetchOne($sql, [
            ':code' => self::$DQI_JOB_NAME,
            ':status' => Status::IN_PROGRESS,
        ]);
    }

    private function shouldBeExecuted(): bool
    {
        foreach ($this->steps as $step) {
            if ($step->shouldBeExecuted()) {
                return true;
            }
        }

        return false;
    }
}
