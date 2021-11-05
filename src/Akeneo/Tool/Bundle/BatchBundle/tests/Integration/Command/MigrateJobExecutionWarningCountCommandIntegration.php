<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\BatchBundle\tests\Integration\Command;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class MigrateJobExecutionWarningCountCommandIntegration extends TestCase
{
    private Connection $dbConnection;
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dbConnection = $this->get('database_connection');
        $this->em = $this->get('doctrine.orm.default_entity_manager');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_migrates_all_job_execution_warning_count()
    {
        $jobInstance = $this->givenAJobInstance();

        $jobExecutionWithoutWarnings = $this->givenAJobExecutionWithoutWarnings($jobInstance);
        $jobExecutionWithWarnings1 = $this->givenAJobExecutionWithWarnings($jobInstance, 5);
        $jobExecutionWithWarnings2 = $this->givenAJobExecutionWithWarnings($jobInstance, 3);

        $this->resetAllWarningCount();

        $this->runMigrationCommand();

        $this->assertMigrationStatusIsPersisted();

        $this->assertStepExecutionWarningCountEquals($jobExecutionWithoutWarnings, 0);
        $this->assertStepExecutionWarningCountEquals($jobExecutionWithWarnings1, 5);
        $this->assertStepExecutionWarningCountEquals($jobExecutionWithWarnings2, 3);
    }

    private function assertStepExecutionWarningCountEquals(JobExecution $jobExecution, int $expectedCount): void
    {
        $query = <<<SQL
SELECT warning_count FROM akeneo_batch_step_execution WHERE id = :id;
SQL;

        foreach ($jobExecution->getStepExecutions() as $stepExecution) {
            $stepExecutionWarningCount = $this->dbConnection->executeQuery(
                $query,
                ['id' => $stepExecution->getId()]
            )->fetchOne();

            $this->assertSame($expectedCount, intval($stepExecutionWarningCount));
        }
    }

    private function assertMigrationStatusIsPersisted(): void
    {
        $query = <<<SQL
SELECT status FROM pim_one_time_task WHERE code = :code;
SQL;

        $status = $this->dbConnection->executeQuery(
            $query,
            ['code' => 'akeneo:batch:migrate-job-execution-warning-count']
        )->fetchOne();

        $this->assertSame('done', $status);
    }

    private function givenAJobInstance(): JobInstance
    {
        $jobs = $this->get('pim_enrich.repository.job_instance')->findAll();

        return $jobs[0];
    }

    private function givenAJobExecutionWithoutWarnings(JobInstance $jobInstance): JobExecution
    {
        $jobExecution = new JobExecution();
        $jobExecution->setJobInstance($jobInstance);

        $stepExecution1 = new StepExecution('step_1', $jobExecution);
        $stepExecution2 = new StepExecution('step_2', $jobExecution);

        $jobExecution->addStepExecution($stepExecution1)->addStepExecution($stepExecution2);

        $this->em->persist($stepExecution1);
        $this->em->persist($stepExecution2);
        $this->em->persist($jobExecution);
        $this->em->flush();

        return $jobExecution;
    }

    private function givenAJobExecutionWithWarnings(JobInstance $jobInstance, int $warningCount): JobExecution
    {
        $jobExecution = new JobExecution();
        $jobExecution->setJobInstance($jobInstance);

        $stepExecution1 = new StepExecution('step_1', $jobExecution);
        for ($i = 1; $i <= $warningCount; $i++) {
            $stepExecution1->addWarning(sprintf('step_1_warning_%d', $i), [], new DataInvalidItem([]));
        }

        $stepExecution2 = new StepExecution('step_2', $jobExecution);
        for ($i = 1; $i <= $warningCount; $i++) {
            $stepExecution2->addWarning(sprintf('step_2_warning_%d', $i), [], new DataInvalidItem([]));
        }

        $jobExecution->addStepExecution($stepExecution1)->addStepExecution($stepExecution2);

        $this->em->persist($stepExecution1);
        $this->em->persist($stepExecution2);
        $this->em->persist($jobExecution);
        $this->em->flush();

        return $jobExecution;
    }

    private function resetAllWarningCount(): void
    {
        $this->dbConnection->executeQuery(
            "UPDATE akeneo_batch_step_execution SET warning_count = 0;"
        );
    }

    private function runMigrationCommand(): void
    {
        $application = new Application($this->get('kernel'));
        $application->setAutoExit(false);

        $input = new ArrayInput([
            'command'  => 'akeneo:batch:migrate-job-execution-warning-count',
            '--bulk-size' => 3,
            '--no-interaction' => true,
            '-v'
        ]);

        $output = new BufferedOutput();
        $exitCode = $application->run($input, $output);

        self::assertSame(0, $exitCode, sprintf('Migration failed, "%s".', $output->fetch()));
    }
}
