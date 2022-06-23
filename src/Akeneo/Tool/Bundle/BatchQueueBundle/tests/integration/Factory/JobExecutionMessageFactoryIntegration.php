<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\BatchQueueBundle\tests\integration\Factory;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\BatchQueue\Factory\JobExecutionMessageFactory;
use Akeneo\Tool\Component\BatchQueue\Queue\DataMaintenanceJobExecutionMessage;
use Akeneo\Tool\Component\BatchQueue\Queue\ExportJobExecutionMessage;
use Akeneo\Tool\Component\BatchQueue\Queue\ImportJobExecutionMessage;
use Akeneo\Tool\Component\BatchQueue\Queue\UiJobExecutionMessage;

final class JobExecutionMessageFactoryIntegration extends TestCase
{
    private JobExecutionMessageFactory $jobExecutionMessageFactory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->jobExecutionMessageFactory = $this->get('akeneo_batch_queue.factory.job_execution_message');
    }

    public function test_it_returns_a_ui_job_message(): void
    {
        $jobExecution = $this->createJobExecution('update_product_value');
        $jobExecutionMessage = $this->jobExecutionMessageFactory->buildFromNormalized(
            [
                'id' => '215ee791-1c40-4c60-82fb-cb017d6bcb90',
                'job_execution_id' => $jobExecution->getId(),
                'created_time' => '2021-03-08T15:37:23+01:00',
                'updated_time' => null,
                'options' => ['option1' => 'value1'],
            ],
            UiJobExecutionMessage::class
        );
        self::assertInstanceOf(UiJobExecutionMessage::class, $jobExecutionMessage);
        self::assertEquals('215ee791-1c40-4c60-82fb-cb017d6bcb90', $jobExecutionMessage->getId()->toString());
        self::assertEquals($jobExecution->getId(), $jobExecutionMessage->getJobExecutionId());
        self::assertEquals(new \DateTime('2021-03-08T15:37:23+01:00'), $jobExecutionMessage->getCreateTime());
        self::assertNull($jobExecutionMessage->getUpdatedTime());
        self::assertEquals(['option1' => 'value1'], $jobExecutionMessage->getOptions());
    }

    public function test_it_returns_an_import_job_message(): void
    {
        $jobExecution = $this->createJobExecution('csv_product_import');
        $jobExecutionMessage = $this->jobExecutionMessageFactory->buildFromNormalized(
            [
                'id' => '215ee791-1c40-4c60-82fb-cb017d6bcb90',
                'job_execution_id' => $jobExecution->getId(),
                'created_time' => '2021-03-08T15:37:23+01:00',
                'updated_time' => '2021-03-10T15:37:23+01:00',
                'options' => ['option1' => 'value1', 'option2' => 'value2'],
            ],
            ImportJobExecutionMessage::class
        );
        self::assertInstanceOf(ImportJobExecutionMessage::class, $jobExecutionMessage);
        self::assertEquals('215ee791-1c40-4c60-82fb-cb017d6bcb90', $jobExecutionMessage->getId()->toString());
        self::assertEquals($jobExecution->getId(), $jobExecutionMessage->getJobExecutionId());
        self::assertEquals(new \DateTime('2021-03-08T15:37:23+01:00'), $jobExecutionMessage->getCreateTime());
        self::assertEquals(new \DateTime('2021-03-10T15:37:23+01:00'), $jobExecutionMessage->getUpdatedTime());
        self::assertEquals(['option1' => 'value1', 'option2' => 'value2'], $jobExecutionMessage->getOptions());
    }

    public function test_it_returns_an_export_job_message(): void
    {
        $jobExecution = $this->createJobExecution('csv_product_export');
        $jobExecutionMessage = $this->jobExecutionMessageFactory->buildFromNormalized(
            [
                'id' => '215ee791-1c40-4c60-82fb-cb017d6bcb90',
                'job_execution_id' => $jobExecution->getId(),
                'created_time' => '2021-03-08T15:37:23+01:00',
                'updated_time' => null,
                'options' => ['option1' => 'value1'],
            ],
            ExportJobExecutionMessage::class
        );
        self::assertInstanceOf(ExportJobExecutionMessage::class, $jobExecutionMessage);
    }

    public function test_it_returns_a_backend_job_message(): void
    {
        $jobExecution = $this->createJobExecution('compute_completeness_of_products_family');
        $jobExecutionMessage = $this->jobExecutionMessageFactory->buildFromNormalized(
            [
                'id' => '215ee791-1c40-4c60-82fb-cb017d6bcb90',
                'job_execution_id' => $jobExecution->getId(),
                'created_time' => '2021-03-08T15:37:23+01:00',
                'updated_time' => null,
                'options' => ['option1' => 'value1'],
            ],
            null
        );
        self::assertInstanceOf(DataMaintenanceJobExecutionMessage::class, $jobExecutionMessage);
    }

    protected function createJobExecution(
        string $jobInstanceCode,
        array $configuration = []
    ): JobExecution {
        $jobInstance = $this->get('pim_enrich.repository.job_instance')->findOneBy(['code' => $jobInstanceCode]);

        $job = $this->get('akeneo_batch.job.job_registry')->get($jobInstanceCode);
        $configuration = array_merge($jobInstance->getRawParameters(), $configuration);
        $jobParameters = $this->get('akeneo_batch.job_parameters_factory')->create($job, $configuration);

        return $this->get('akeneo_batch.job_repository')->createJobExecution($job, $jobInstance, $jobParameters);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
