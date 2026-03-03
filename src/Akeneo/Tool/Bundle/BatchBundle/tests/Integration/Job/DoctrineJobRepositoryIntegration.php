<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\BatchBundle\tests\Integration\Job;

use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Akeneo\Platform\Bundle\ImportExportBundle\Repository\InternalApi\JobExecutionRepository;
use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;
use Akeneo\Tool\Bundle\BatchBundle\Job\DoctrineJobRepository;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Akeneo\Tool\Component\Batch\Job\ExitStatus;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Model\Warning;
use Doctrine\ORM\EntityManager;

class DoctrineJobRepositoryIntegration extends TestCase
{
    public function test_create_job_execution_with_job_parameters()
    {
        $jobInstance = $this->createJobInstance('stoppable_dumb_job');

        $job = $this->get('akeneo_batch.job.job_registry')->get('stoppable_dumb_job');
        $jobParameters =  new JobParameters(['foo' => 'bar']);
        $jobExecution = $this->getDoctrineJobRepository()->createJobExecution($job, $jobInstance, $jobParameters);

        $result = $this->selectJobExecution($jobExecution->getId());

        $expectedResult = [
            'status' => '2',
            'exit_code' => 'UNKNOWN',
            'raw_parameters' => '{"foo": "bar"}',
            'is_stoppable' => '1',
            'is_visible' => '1',
        ];

        $this->assertEquals($expectedResult['status'], $result['status']);
        $this->assertEquals($expectedResult['exit_code'], $result['exit_code']);
        $this->assertJsonStringEqualsJsonString($expectedResult['raw_parameters'], $result['raw_parameters']);
        $this->assertEquals($expectedResult['is_stoppable'], $result['is_stoppable']);
        $this->assertEquals($expectedResult['is_visible'], $result['is_visible']);
    }


    public function test_create_non_stoppable_job_execution()
    {
        $jobInstance = $this->createJobInstance('non_stoppable_dumb_job');
        $job = $this->get('akeneo_batch.job.job_registry')->get('non_stoppable_dumb_job');

        $jobParameters =  new JobParameters([]);
        $jobExecution = $this->getDoctrineJobRepository()->createJobExecution($job, $jobInstance, $jobParameters);

        $result = $this->selectJobExecution($jobExecution->getId());

        $this->assertEquals('0', $result['is_stoppable']);
    }

    public function test_get_last_job_execution_with_job_parameters()
    {
        $jobInstance = $this->createJobInstance('stoppable_dumb_job');
        $job = $this->get('akeneo_batch.job.job_registry')->get('stoppable_dumb_job');

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

    public function test_add_warnings()
    {
        $jobInstance = $this->createJobInstance('stoppable_dumb_job');
        $job = $this->get('akeneo_batch.job.job_registry')->get('stoppable_dumb_job');
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

    public function test_job_execution_is_visible_when_feature_of_the_job_is_activated()
    {
        $this->addNewFeatureFlag('coffee_maker');
        $this->get('feature_flags')->enable('coffee_maker');

        $jobInstance = $this->createJobInstance('visible_coffee_maker_job');

        $job = $this->get('akeneo_batch.job.job_registry')->get('visible_coffee_maker_job');

        $jobParameters =  new JobParameters([]);
        $jobExecution = $this->getDoctrineJobRepository()->createJobExecution($job, $jobInstance, $jobParameters);

        $result = $this->selectJobExecution($jobExecution->getId());

        $this->assertEquals('1', $result['is_visible']);
    }

    /**
     * A job is not visible if the feature is not activated. If the feature is activated afterward, due to an upgrade in the offer,
     * then the job executed before the activation should still be not visible.
     */
    public function test_that_job_execution_is_not_visible_when_feature_of_the_job_is_not_activated()
    {
        $this->addNewFeatureFlag('coffee_maker');
        $this->get('feature_flags')->disable('coffee_maker');

        $jobInstance = $this->createJobInstance('visible_coffee_maker_job');

        $job = $this->get('akeneo_batch.job.job_registry')->get('visible_coffee_maker_job');

        $jobParameters =  new JobParameters([]);
        $jobExecution = $this->getDoctrineJobRepository()->createJobExecution($job, $jobInstance, $jobParameters);

        $result = $this->selectJobExecution($jobExecution->getId());

        $this->assertEquals('0', $result['is_visible']);
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
        return $this->catalog->useMinimalCatalog();
    }

    private function selectJobExecution(int $id): array {
        $connection = $this->get('doctrine.orm.default_entity_manager')->getConnection();
        $stmt = $connection->prepare('SELECT * from akeneo_batch_job_execution where id = :id');
        $stmt->bindParam('id', $id);
        $stmt->execute();
        return $stmt->fetch();

    }

    private function createJobInstance(string $jobName): JobInstance
    {
        $jobInstance = new JobInstance('import', 'test', $jobName);
        $jobInstance->setCode('stoppable_dumb_job_instance');
        $jobInstanceSaver = $this->get('akeneo_batch.saver.job_instance');
        $jobInstanceSaver->save($jobInstance);

        return $jobInstance;
    }

    /**
     * It's not possible to activate a feature flag that does not exist, to avoid to mock a non existing flag in production
     * Therefore we create a fake service in the feature flag registry. The fake implementation is not used.
     */
    private function addNewFeatureFlag(string $featureFlag): void
    {
        $featureFlagService = new class implements FeatureFlag
        {
            public function isEnabled(?string $feature = null): bool
            {
                return false;
            }
        };
        $this->get('akeneo.feature_flag.service.registry')->add($featureFlag, $featureFlagService);
    }
}
