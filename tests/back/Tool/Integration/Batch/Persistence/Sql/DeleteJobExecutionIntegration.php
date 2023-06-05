<?php

declare(strict_types=1);

namespace Akeneo\Test\Tool\Integration\Batch\Persistence\Sql;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\BatchBundle\Persistence\Sql\DeleteJobExecution;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class DeleteJobExecutionIntegration extends TestCase
{
    public function test_that_it_deletes_all_jobs_older_than_one_day_except_the_most_recent_one_for_each_job_instance()
    {
        $this->launchJob('csv_product_export');
        $this->launchJob('csv_product_export');
        $this->assertJobExecutionCount(2);

        $this->getConnection()->executeStatement('UPDATE akeneo_batch_job_execution SET create_time = Date_ADD(end_time, INTERVAL -10 day)');

        $this->getDeleteQuery()->olderThanDays(1, [], null);

        $this->assertJobExecutionCount(1);
    }

    public function test_that_it_deletes_all_jobs_older_than_one_day_except_the_most_recent_one_for_given_job_instance_code()
    {
        $this->launchJob('csv_product_export');
        $this->launchJob('csv_product_export');
        $this->launchJob('csv_product_model_export');
        $this->launchJob('csv_product_model_export');
        $this->assertJobExecutionCount(4);

        $this->getConnection()->executeStatement('UPDATE akeneo_batch_job_execution SET create_time = Date_ADD(end_time, INTERVAL -10 day)');

        $this->getDeleteQuery()->olderThanDays(1, ['csv_product_model_export'], null);

        $this->assertJobExecutionCount(2, ['csv_product_export']);
        $this->assertJobExecutionCount(1, ['csv_product_model_export']);
    }

    public function test_that_it_deletes_all_jobs()
    {
        $this->launchJob('csv_product_export');
        $this->launchJob('csv_product_export');
        $this->assertJobExecutionCount(2);

        $this->getDeleteQuery()->all([], null);

        $this->assertJobExecutionCount(0);
    }

    public function test_that_it_deletes_all_jobs_by_instance_codes()
    {
        $this->launchJob('csv_product_export');
        $this->launchJob('csv_product_export');
        $this->launchJob('csv_product_model_export');
        $this->assertJobExecutionCount(3);

        $this->getDeleteQuery()->all(['csv_product_export'], null);

        $this->assertJobExecutionCount(0, ['csv_product_export']);
        $this->assertJobExecutionCount(1, ['csv_product_model_export']);
    }

    public function test_that_it_deletes_all_jobs_by_status()
    {
        $this->launchJob('csv_product_export');
        $this->launchJob('csv_product_export');
        $this->assertJobExecutionCount(2);

        $this->getConnection()->executeStatement('UPDATE akeneo_batch_job_execution SET status = '.BatchStatus::FAILED);

        $this->getDeleteQuery()->all([], new BatchStatus(BatchStatus::FAILED));

        $this->assertJobExecutionCount(0);
    }

    public function test_that_it_deletes_all_jobs_by_instance_codes_and_status()
    {
        $this->launchJob('csv_product_export');
        $this->launchJob('csv_product_export');
        $this->launchJob('csv_product_model_export');
        $this->assertJobExecutionCount(3);

        $this->getConnection()->executeStatement('UPDATE akeneo_batch_job_execution SET status = '.BatchStatus::FAILED);

        $this->getDeleteQuery()->all(['csv_product_model_export'], new BatchStatus(BatchStatus::FAILED));

        $this->assertJobExecutionCount(2);
    }

    private function getDeleteQuery(): DeleteJobExecution
    {
        return $this->get('akeneo_batch.delete_job_execution');
    }

    private function launchJob(string $jobInstanceCode): void
    {
        $application = new Application(static::$kernel);
        $application->setAutoExit(false);

        $parameters = [
            'command'  => 'akeneo:batch:job',
            'code'     => $jobInstanceCode,
        ];

        $input = new ArrayInput($parameters);
        $output = new BufferedOutput();
        $application->run($input, $output);
    }

    private function assertJobExecutionCount(int $expectedCount, array $jobInstanceCodes = null): void
    {
        $query = 'SELECT COUNT(*) FROM akeneo_batch_job_execution';

        if (!empty($jobInstanceCodes)) {
            $query .= ' WHERE job_instance_id IN (
                SELECT ji.id
                FROM akeneo_batch_job_instance ji
                WHERE ji.code IN (:job_instance_codes)
            )';
        }

        $numberOfJobs = (int) $this->getConnection()->executeQuery(
            $query,
            ['job_instance_codes' => $jobInstanceCodes],
            ['job_instance_codes' => Connection::PARAM_STR_ARRAY]
        )->fetchOne();
        Assert::assertSame($expectedCount, $numberOfJobs);
    }

    private function getConnection(): Connection
    {
        return $this->get('database_connection');
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
