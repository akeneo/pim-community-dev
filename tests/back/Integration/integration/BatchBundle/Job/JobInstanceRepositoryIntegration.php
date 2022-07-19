<?php

declare(strict_types=1);

namespace Akeneo\Test\Integration\integration\BatchBundle\Job;

use Akeneo\Tool\Bundle\BatchBundle\Job\DoctrineJobRepository;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Akeneo\Tool\Component\Batch\Job\ExitStatus;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

class JobInstanceRepositoryIntegration extends TestCase
{
    public function testItDeleteAJobInstanceAndRemovesItJobExecutions(): void
    {
        $jobInstanceCode = 'csv_product_export';
        $jobExecutionId = $this->createJobExecution($jobInstanceCode);

        $this->assertTrue($this->jobInstanceExists($jobInstanceCode));
        $this->assertTrue($this->jobExecutionExists($jobExecutionId));

        $this->getJobInstanceRepository()->remove($jobInstanceCode);

        $this->assertFalse($this->jobInstanceExists($jobInstanceCode));
        $this->assertFalse($this->jobExecutionExists($jobExecutionId));
    }

    private function createJobExecution(string $jobInstanceCode): int
    {
        $jobInstance = $this->getJobInstanceRepository()->findOneByIdentifier($jobInstanceCode);
        $job = $this->get('akeneo_batch.job.job_registry')->get($jobInstanceCode);
        $jobParameters =  new JobParameters([]);
        $jobExecution = $this->getDoctrineJobRepository()->createJobExecution($job, $jobInstance, $jobParameters);
        return $jobExecution->getId();
    }

    private function jobInstanceExists(string $jobInstanceCode): bool {
        $sql = <<<SQL
    SELECT count(*) FROM akeneo_batch_job_instance WHERE code = :code
SQL;

        $stmt = $this->getConnection()->executeQuery($sql, ['code' => $jobInstanceCode]);
        $count = $stmt->fetchOne();

        return 0 < $count;
    }

    private function jobExecutionExists(int $jobExecutionId): bool {
        $sql = <<<SQL
    SELECT count(*) FROM akeneo_batch_job_execution WHERE id = :id
SQL;

        $stmt = $this->getConnection()->executeQuery($sql, ['id' => $jobExecutionId]);
        $count = $stmt->fetchOne();

        return 0 < $count;
    }

    private function getDoctrineJobRepository(): DoctrineJobRepository
    {
        return $this->get('akeneo_batch.job_repository');
    }

    private function getJobInstanceRepository(): JobInstanceRepository
    {
        return $this->get('akeneo_batch.job.job_instance_repository');
    }

    private function getConnection(): Connection
    {
        return $this->get('database_connection');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
