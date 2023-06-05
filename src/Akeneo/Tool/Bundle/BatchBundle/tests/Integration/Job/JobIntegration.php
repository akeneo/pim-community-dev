<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\BatchBundle\tests\Integration\Job;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Akeneo\Tool\Component\Batch\Job\Job;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class JobIntegration extends TestCase
{
    public function test_it_executes_a_starting_job_execution(): void
    {
        $steps = [
            $this->createStep('step_1', BatchStatus::COMPLETED),
            $this->createStep('step_2', BatchStatus::COMPLETED),
        ];

        $job = $this->createJob('a_starting_job', $steps);
        $jobExecution = $this->createJobExecution($job);

        $this->assertCount(0, $jobExecution->getStepExecutions());
        $this->assertSame(BatchStatus::STARTING, $jobExecution->getStatus()->getValue());

        $job->execute($jobExecution);

        $this->assertCount(2, $jobExecution->getStepExecutions());
        $this->assertSame(BatchStatus::COMPLETED, $jobExecution->getStatus()->getValue());
    }

    public function test_it_handles_when_a_job_is_being_paused(): void
    {
        $steps = [
            $this->createStep('step_1', BatchStatus::COMPLETED),
            $this->createStep('step_2', BatchStatus::PAUSED),
            $this->createStep('step_3'),
        ];

        $job = $this->createJob('a_starting_job_being_paused', $steps);
        $jobExecution = $this->createJobExecution($job);

        $this->assertCount(0, $jobExecution->getStepExecutions());
        $this->assertSame(BatchStatus::STARTING, $jobExecution->getStatus()->getValue());

        $job->execute($jobExecution);

        $this->assertCount(2, $jobExecution->getStepExecutions());
        $this->assertSame(BatchStatus::PAUSED, $jobExecution->getStatus()->getValue());
    }

    public function test_it_resumes_a_paused_job(): void
    {
        $steps = [
            $this->createStep('step_1', BatchStatus::COMPLETED),
            $this->createStep('step_2', BatchStatus::COMPLETED),
            $this->createStep('step_3', BatchStatus::COMPLETED),
        ];

        $job = $this->createJob('a_paused_job', $steps);
        $jobExecution = $this->createJobExecution($job);

        $this->createStepExecution($jobExecution, 'step_1', BatchStatus::COMPLETED);
        $this->createStepExecution($jobExecution, 'step_2', BatchStatus::PAUSED);

        $this->assertCount(2, $jobExecution->getStepExecutions());
        $this->assertSame(BatchStatus::PAUSED, $jobExecution->getStatus()->getValue());

        $job->execute($jobExecution);

        $this->assertCount(3, $jobExecution->getStepExecutions());
        $this->assertSame(BatchStatus::COMPLETED, $jobExecution->getStatus()->getValue());
    }

    public function test_it_fails_when_it_resumes_a_paused_job_and_a_new_step_has_been_added_to_the_job(): void
    {
        $steps = [
            $this->createStep('step_0'),
            $this->createStep('step_1'),
            $this->createStep('step_2'),
            $this->createStep('step_3'),
        ];

        $job = $this->createJob('a_paused_job', $steps);
        $jobExecution = $this->createJobExecution($job);

        $this->createStepExecution($jobExecution, 'step_1', BatchStatus::COMPLETED);
        $this->createStepExecution($jobExecution, 'step_2', BatchStatus::PAUSED);

        $this->assertCount(2, $jobExecution->getStepExecutions());
        $this->assertSame(BatchStatus::PAUSED, $jobExecution->getStatus()->getValue());

        $job->execute($jobExecution);

        $this->assertCount(2, $jobExecution->getStepExecutions());
        $this->assertSame(BatchStatus::FAILED, $jobExecution->getStatus()->getValue());
        $this->assertSame('The job is corrupted', $jobExecution->getFailureExceptions()[0]['message']);
    }

    public function test_it_fails_when_it_resumes_a_paused_job_and_a_step_has_been_removed_to_the_job(): void
    {
        $steps = [
            $this->createStep('step_2'),
            $this->createStep('step_3'),
        ];

        $job = $this->createJob('a_paused_job', $steps);
        $jobExecution = $this->createJobExecution($job);

        $this->createStepExecution($jobExecution, 'step_1', BatchStatus::COMPLETED);
        $this->createStepExecution($jobExecution, 'step_2', BatchStatus::PAUSED);

        $this->assertCount(2, $jobExecution->getStepExecutions());
        $this->assertSame(BatchStatus::PAUSED, $jobExecution->getStatus()->getValue());

        $job->execute($jobExecution);

        $this->assertCount(2, $jobExecution->getStepExecutions());
        $this->assertSame(BatchStatus::FAILED, $jobExecution->getStatus()->getValue());
        $this->assertSame('The job is corrupted', $jobExecution->getFailureExceptions()[0]['message']);
    }

    /**
     * @param StepInterface[] $steps
     */
    private function createJob(string $jobName, array $steps): Job
    {
        return new Job(
            $jobName,
            $this->get('event_dispatcher'),
            $this->get('akeneo_batch.job_repository'),
            $steps,
        );
    }

    private function createJobExecution(JobInterface $job): JobExecution
    {
        $connector = 'A connector';
        $type = 'export';
        $jobInstance = new JobInstance($connector, $type, $job->getName());
        $jobInstance->setCode($job->getName());

        $this->get('akeneo_batch.job.job_registry')->register($job, $type, $connector);

        return $this->get('akeneo_batch.job_repository')->createJobExecution($job, $jobInstance, new JobParameters([]));
    }

    private function createStepExecution(JobExecution $jobExecution, string $stepName, int $executionStatus): void
    {
        $stepExecution = $jobExecution->createStepExecution($stepName);
        $stepExecution->setStatus(new BatchStatus($executionStatus));
        $jobExecution->setStatus($stepExecution->getStatus());
    }

    private function createStep(string $name, ?int $executionStatus = null): StepInterface
    {
        return new class($name, $executionStatus) implements StepInterface {
            public function __construct(
                private readonly string $name,
                private readonly ?int $executionStatus,
            ) {}

            public function getName(): string
            {
                return $this->name;
            }

            public function execute(StepExecution $stepExecution): void
            {
                if (null === $this->executionStatus) {
                    return;
                }
                $stepExecution->setStatus(new BatchStatus($this->executionStatus));
            }
        };
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
