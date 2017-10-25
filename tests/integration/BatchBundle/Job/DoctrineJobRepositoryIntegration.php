<?php

declare(strict_types=1);

namespace Akeneo\Test\Integration\integration\BatchBundle\Job;

use Akeneo\Bundle\BatchBundle\Job\DoctrineJobRepository;
use Akeneo\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Component\Batch\Job\BatchStatus;
use Akeneo\Component\Batch\Job\ExitStatus;
use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

class DoctrineJobRepositoryIntegration extends TestCase
{
    public function testCreateJobExecutionWithJobParameters()
    {
        $connection = $this->get('doctrine.orm.default_entity_manager')->getConnection();

        $jobInstance = $this->getJobInstanceRepository()->findOneByIdentifier('csv_product_quick_export');

        $jobParameters =  new JobParameters(['foo' => 'bar']);
        $jobExecution = $this->getDoctrineJobRepository()->createJobExecution($jobInstance, $jobParameters);

        $jobExecutionId = $jobExecution->getId();
        $stmt = $connection->prepare('SELECT status, exit_code, raw_parameters from akeneo_batch_job_execution where id = :id');
        $stmt->bindParam('id', $jobExecutionId);
        $stmt->execute();
        $result = $stmt->fetch();

        $expectedResult = [
            'status' => 2,
            'exit_code' => 'UNKNOWN',
            'raw_parameters' => '{"foo":"bar"}',
        ];

        $this->assertEquals($expectedResult, $result);
    }

    public function testGetLastJobExecutionWithJobParameters()
    {
        $jobInstance = $this->getJobInstanceRepository()->findOneByIdentifier('csv_product_quick_export');

        $jobParameters =  new JobParameters(['foo' => 'bar']);
        $jobExecution = $this->getDoctrineJobRepository()->createJobExecution($jobInstance, $jobParameters);
        $this->getDoctrineJobRepository()->getJobManager()->clear();

        $lastJobExecution = $this->getDoctrineJobRepository()->getLastJobExecution($jobInstance, BatchStatus::STARTING);

        $this->assertEquals($jobExecution->getId(), $lastJobExecution->getId());
        $this->assertEquals($jobInstance->getId(), $lastJobExecution->getJobInstance()->getId());
        $this->assertEquals(BatchStatus::STARTING, $lastJobExecution->getStatus()->getValue());
        $this->assertEquals(ExitStatus::UNKNOWN, $lastJobExecution->getExitStatus()->getExitCode());
        $this->assertEquals(['foo' => 'bar'], $lastJobExecution->getRawParameters());
        $this->assertEquals(['foo' => 'bar'], $lastJobExecution->getJobParameters()->all());
    }

    /**
     * @return DoctrineJobRepository
     */
    protected function getDoctrineJobRepository(): DoctrineJobRepository
    {
        return $this->get('akeneo_batch.job_repository');
    }

    /**
     * @return JobInstanceRepository
     */
    protected function getJobInstanceRepository(): JobInstanceRepository
    {
        return $this->get('akeneo_batch.job.job_instance_repository');
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalSqlCatalog();
    }
}
