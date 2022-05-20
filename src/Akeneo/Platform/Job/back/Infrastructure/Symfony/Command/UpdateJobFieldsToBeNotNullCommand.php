<?php

namespace Akeneo\Platform\Job\Infrastructure\Symfony\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdateJobFieldsToBeNotNullCommand extends Command
{
    protected static $defaultName = 'pim:migrate:update-job-fields-not-null';
    protected static $defaultDescription = 'Update some fields on StepExecution and JobExecution tables to be NOT NULL.';

    public function __construct(
        private Connection $dbConnection
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setHidden(true);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($this->isMigrationDone()) {
            $output->writeln('The update has already been performed.');

            return Command::SUCCESS;
        }

        $output->writeln('Start update of fields...');

        $this->dbConnection->executeQuery(
            <<<SQL
UPDATE akeneo_batch_job_execution SET is_stoppable = 0 WHERE is_stoppable IS NULL;
UPDATE akeneo_batch_job_execution SET step_count = 1 WHERE step_count IS NULL;
UPDATE akeneo_batch_job_execution SET is_visible = 1 WHERE is_visible IS NULL;
UPDATE akeneo_batch_step_execution SET is_trackable = 0 WHERE is_trackable IS NULL;

ALTER TABLE akeneo_batch_job_execution MODIFY COLUMN is_stoppable TINYINT(1) DEFAULT 0 NOT NULL, ALGORITHM=INPLACE, LOCK=NONE;
ALTER TABLE akeneo_batch_job_execution MODIFY COLUMN step_count INT DEFAULT 1 NOT NULL, ALGORITHM=INPLACE, LOCK=NONE;
ALTER TABLE akeneo_batch_job_execution MODIFY COLUMN is_visible TINYINT(1) DEFAULT 1 NOT NULL, ALGORITHM=INPLACE, LOCK=NONE;
ALTER TABLE akeneo_batch_step_execution MODIFY COLUMN is_trackable TINYINT(1) DEFAULT 0 NOT NULL, ALGORITHM=INPLACE, LOCK=NONE;
SQL
        );

        $output->writeln('Migration done.');

        return Command::SUCCESS;
    }

    private function isMigrationDone(): bool
    {
        $query = <<<SQL
SHOW COLUMNS FROM `akeneo_batch_job_execution` WHERE `Null` = 'YES' AND `Field` IN ('is_stoppable', 'step_count', 'is_visible')
SQL;
        $jobExecutionFieldsToMigrate = $this->dbConnection->executeQuery($query)->fetchAllAssociative();

        $query = <<<SQL
SHOW COLUMNS FROM `akeneo_batch_step_execution` WHERE `Null` = 'YES' AND `Field` = 'is_trackable'
SQL;
        $stepExecutionFieldsToMigrate = $this->dbConnection->executeQuery($query)->fetchAllAssociative();

        return empty($jobExecutionFieldsToMigrate) && empty($stepExecutionFieldsToMigrate);
    }
}
