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
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Model\Warning;

class DoctrineJobRepositoryIntegration extends TestCase
{
    public function testCreateJobExecutionWithJobParameters()
    {
        $jobInstanceCode = 'csv_product_quick_export';
        $jobInstance = $this->getJobInstanceRepository()->findOneByIdentifier($jobInstanceCode);
        $job = $this->get('akeneo_batch.job.job_registry')->get($jobInstanceCode);
        $jobParameters =  new JobParameters(['foo' => 'bar']);
        $jobExecution = $this->getDoctrineJobRepository()->createJobExecution($job, $jobInstance, $jobParameters);

        $result = $this->selectJobExecution($jobExecution->getId());

        $expectedResult = [
            'status' => '2',
            'exit_code' => 'UNKNOWN',
            'raw_parameters' => '{"foo": "bar"}',
            'is_stoppable' => '1',
        ];

        $this->assertEquals($expectedResult['status'], $result['status']);
        $this->assertEquals($expectedResult['exit_code'], $result['exit_code']);
        $this->assertJsonStringEqualsJsonString($expectedResult['raw_parameters'], $result['raw_parameters']);
        $this->assertEquals($expectedResult['is_stoppable'], $result['is_stoppable']);
    }

    public function testCreateNonStoppableJobExecution()
    {
        $jobInstanceCode = 'clean_removed_attribute_job';
        $jobInstance = $this->getJobInstanceRepository()->findOneByIdentifier($jobInstanceCode);
        $job = $this->get('akeneo_batch.job.job_registry')->get($jobInstanceCode);

        $jobParameters =  new JobParameters([]);
        $jobExecution = $this->getDoctrineJobRepository()->createJobExecution($job, $jobInstance, $jobParameters);

        $result = $this->selectJobExecution($jobExecution->getId());

        $this->assertEquals('0', $result['is_stoppable']);
    }

    public function testGetLastJobExecutionWithJobParameters()
    {
        $jobInstanceCode = 'csv_product_quick_export';
        $jobInstance = $this->getJobInstanceRepository()->findOneByIdentifier($jobInstanceCode);
        $job = $this->get('akeneo_batch.job.job_registry')->get($jobInstanceCode);
        $jobParameters =  new JobParameters(['foo' => 'bar']);
        $jobExecution = $this->getDoctrineJobRepository()->createJobExecution($job, $jobInstance, $jobParameters);

        $this->getDoctrineJobRepository()->getJobManager()->clear();
        $lastJobExecution = $this->getDoctrineJobRepository()->getLastJobExecution($jobInstance, BatchStatus::STARTING);

        $this->assertEquals($jobExecution->getId(), $lastJobExecution->getId());
        $this->assertEquals($jobInstance->getId(), $lastJobExecution->getJobInstance()->getId());
        $this->assertEquals(BatchStatus::STARTING, $lastJobExecution->getStatus()->getValue());
        $this->assertEquals(ExitStatus::UNKNOWN, $lastJobExecution->getExitStatus()->getExitCode());
        $this->assertEquals(['foo' => 'bar'], $lastJobExecution->getRawParameters());
        $this->assertEquals(['foo' => 'bar'], $lastJobExecution->getJobParameters()->all());
    }

    public function testAddWarnings()
    {
        $jobInstanceCode = 'csv_product_quick_export';
        $jobInstance = $this->getJobInstanceRepository()->findOneByIdentifier($jobInstanceCode);
        $job = $this->get('akeneo_batch.job.job_registry')->get($jobInstanceCode);
        $jobParameters =  new JobParameters([]);
        $jobExecution = $this->getDoctrineJobRepository()->createJobExecution($job, $jobInstance, $jobParameters);

        $stepExecution = new StepExecution('step_with_warnings', $jobExecution);
        $this->getDoctrineJobRepository()->updateStepExecution($stepExecution);
        $jobExecution->addStepExecution($stepExecution);
        $this->getDoctrineJobRepository()->updateJobExecution($jobExecution);

        $warningCount = 5;
        $warnings = [];

        for ($i = 0; $i < $warningCount; $i++) {
            $warnings[] = new Warning(
                $stepExecution,
                sprintf('Error {{ param }} %s', $i),
                ['param' => 'value'],
                ['name' => sprintf('Invalid item %s', $i)]
            );
        }

        $this->getDoctrineJobRepository()->addWarnings($stepExecution, $warnings);
        $this->getDoctrineJobRepository()->getJobManager()->clear();

        $lastJobExecution = $this->getDoctrineJobRepository()->getLastJobExecution($jobInstance, BatchStatus::STARTING);
        $stepExecutionWithWarnings = $lastJobExecution->getStepExecutions()->first();

        $this->assertCount($warningCount, $stepExecutionWithWarnings->getWarnings());
        $this->assertEquals($warningCount, $stepExecutionWithWarnings->getWarningCount());

        $firstWarning = $stepExecutionWithWarnings->getWarnings()[0];

        $this->assertEquals('Error {{ param }} 0', $firstWarning->getReason());
        $this->assertEquals(['param' => 'value'], $firstWarning->getReasonParameters());
        $this->assertEquals(['name' => 'Invalid item 0'], $firstWarning->getItem());
    }

    protected function getDoctrineJobRepository(): DoctrineJobRepository
    {
        return $this->get('akeneo_batch.job_repository');
    }

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

    private function selectJobExecution(int $id): array {
        $connection = $this->get('doctrine.orm.default_entity_manager')->getConnection();
        $stmt = $connection->prepare('SELECT * from akeneo_batch_job_execution where id = :id');
        $stmt->bindParam('id', $id);
        $stmt->execute();
        return $stmt->fetch();

    }
}
