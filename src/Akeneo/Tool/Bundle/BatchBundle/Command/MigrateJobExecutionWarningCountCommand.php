<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\BatchBundle\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class MigrateJobExecutionWarningCountCommand extends Command
{
    private const BULK_SIZE = 1000;

    protected static $defaultName = 'akeneo:batch:migrate-job-execution-warning-count';

    private Connection $dbConnection;

    public function __construct(Connection $dbConnection)
    {
        parent::__construct();

        $this->dbConnection = $dbConnection;
    }

    protected function configure()
    {
        $this
            ->setDescription('Calculate and store the number of warnings for every job step execution')
            ->addOption('bulk-size', null, InputOption::VALUE_REQUIRED, 'Bulk size', self::BULK_SIZE)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->migrationCanBeStarted()) {
            $output->writeln('The migration has already been performed or is in progress.');
            return 0;
        }

        $output->writeln('Start migration...');
        $this->persistMigrationStart();

        $lastId = 0;
        $bulkSize = intval($input->getOption('bulk-size'));

        while ($idBulk = $this->getNextStepExecutionIdBulk($lastId, $bulkSize)) {
            $lastId = intval($idBulk['last_id']);
            $this->updateStepExecutionWarningCount(intval($idBulk['first_id']), $lastId);
        }

        $this->persistMigrationDone();
        $output->writeln('Migration done.');

        return 0;
    }

    private function persistMigrationStart(): void
    {
        $query = <<<SQL
INSERT IGNORE INTO pim_one_time_task (code, status, start_time) VALUES 
(:code, 'started', NOW());
SQL;

        $this->dbConnection->executeQuery($query, ['code' => self::$defaultName]);
    }

    private function persistMigrationDone(): void
    {
        $query = <<<SQL
UPDATE pim_one_time_task 
SET status = 'done', end_time = NOW()
WHERE code = :code;
SQL;

        $this->dbConnection->executeQuery($query, ['code' => self::$defaultName]);
    }

    private function migrationCanBeStarted(): bool
    {
        $query = <<<SQL
SELECT 1 FROM pim_one_time_task WHERE code = :code
SQL;

        return !boolval($this->dbConnection->executeQuery($query, ['code' => self::$defaultName])->fetchOne());
    }

    private function getNextStepExecutionIdBulk(int $lastId, int $bulkSize): ?array
    {
        /*
         * Because of purges, the ids of the step execution are not in a successive order.
         * So we have to use a range of ids.
         */
        $query = <<<SQL
SELECT MIN(id) as first_id, MAX(id) as last_id FROM (
    SELECT id FROM akeneo_batch_step_execution
    WHERE id > :lastId
    LIMIT :bulkSize
) executions_bulk;
SQL;

        $bulkResult = $this->dbConnection->executeQuery(
            $query,
            [
                'lastId' => $lastId,
                'bulkSize' => $bulkSize,
            ],
            [
                'lastId' => \PDO::PARAM_INT,
                'bulkSize' => \PDO::PARAM_INT,
            ]
        )->fetchAssociative();

        return isset($bulkResult['first_id']) && isset($bulkResult['last_id']) ? $bulkResult : null;
    }

    private function updateStepExecutionWarningCount(int $firstId, int $lastId): void
    {
        $countWarningsQuery = <<<SQL
SELECT step_execution_id, count(*) AS warning_count
FROM akeneo_batch_warning 
WHERE step_execution_id BETWEEN :firstId AND :lastId
GROUP BY step_execution_id;
SQL;
        $updateStepExecutionQuery = <<<SQL
UPDATE akeneo_batch_step_execution 
SET warning_count = :warningCount
WHERE id = :id;
SQL;

        $stmt = $this->dbConnection->executeQuery(
            $countWarningsQuery,
            [
                'firstId' => $firstId,
                'lastId' => $lastId,
            ],
            [
                'firstId' => \PDO::PARAM_INT,
                'lastId' => \PDO::PARAM_INT,
            ]
        );

        while ($row = $stmt->fetchAssociative()) {
            $this->dbConnection->executeQuery(
                $updateStepExecutionQuery,
                [
                    'id' => $row['step_execution_id'],
                    'warningCount' => $row['warning_count']
                ],
                [
                    'id' => \PDO::PARAM_INT,
                    'warningCount' => \PDO::PARAM_INT,
                ]
            );
        }
    }
}
