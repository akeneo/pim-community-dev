<?php
declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
        $jobExecutionMessage = $this->jobExecutionMessageFactory->buildFromNormalized([
            'id' => 1,
            'job_execution_id' => $jobExecution->getId(),
            'consumer' => null,
            'created_time' => '2021-03-08T15:37:23+01:00',
            'updated_time' => null,
            'options' => ['option1' => 'value1'],
        ]);
        self::assertInstanceOf(UiJobExecutionMessage::class, $jobExecutionMessage);
        self::assertEquals(1, $jobExecutionMessage->getId());
        self::assertEquals($jobExecution->getId(), $jobExecutionMessage->getJobExecutionId());
        self::assertNull($jobExecutionMessage->getConsumer());
        self::assertEquals(new \DateTime('2021-03-08T15:37:23+01:00'), $jobExecutionMessage->getCreateTime());
        self::assertNull($jobExecutionMessage->getUpdatedTime());
        self::assertEquals(['option1' => 'value1'], $jobExecutionMessage->getOptions());
    }

    public function test_it_returns_an_import_job_message(): void
    {
        $jobExecution = $this->createJobExecution('csv_product_import');
        $jobExecutionMessage = $this->jobExecutionMessageFactory->buildFromNormalized([
            'id' => 1,
            'job_execution_id' => $jobExecution->getId(),
            'consumer' => 'c418363c-eee7-454c-974f-ff91758abebe',
            'created_time' => '2021-03-08T15:37:23+01:00',
            'updated_time' => '2021-03-10T15:37:23+01:00',
            'options' => ['option1' => 'value1', 'option2' => 'value2'],
        ]);
        self::assertInstanceOf(ImportJobExecutionMessage::class, $jobExecutionMessage);
        self::assertEquals(1, $jobExecutionMessage->getId());
        self::assertEquals($jobExecution->getId(), $jobExecutionMessage->getJobExecutionId());
        self::assertEquals('c418363c-eee7-454c-974f-ff91758abebe', $jobExecutionMessage->getConsumer());
        self::assertEquals(new \DateTime('2021-03-08T15:37:23+01:00'), $jobExecutionMessage->getCreateTime());
        self::assertEquals(new \DateTime('2021-03-10T15:37:23+01:00'), $jobExecutionMessage->getUpdatedTime());
        self::assertEquals(['option1' => 'value1', 'option2' => 'value2'], $jobExecutionMessage->getOptions());
    }

    public function test_it_returns_an_export_job_message(): void
    {
        $jobExecution = $this->createJobExecution('csv_product_export');
        $jobExecutionMessage = $this->jobExecutionMessageFactory->buildFromNormalized([
            'id' => 1,
            'job_execution_id' => $jobExecution->getId(),
            'consumer' => null,
            'created_time' => '2021-03-08T15:37:23+01:00',
            'updated_time' => null,
            'options' => ['option1' => 'value1'],
        ]);
        self::assertInstanceOf(ExportJobExecutionMessage::class, $jobExecutionMessage);
    }

    public function test_it_returns_a_backend_job_message(): void
    {
        $jobExecution = $this->createJobExecution('compute_completeness_of_products_family');
        $jobExecutionMessage = $this->jobExecutionMessageFactory->buildFromNormalized([
            'id' => 1,
            'job_execution_id' => $jobExecution->getId(),
            'consumer' => null,
            'created_time' => '2021-03-08T15:37:23+01:00',
            'updated_time' => null,
            'options' => ['option1' => 'value1'],
        ]);
        self::assertInstanceOf(DataMaintenanceJobExecutionMessage::class, $jobExecutionMessage);
    }

    protected function createJobExecution(
        string $jobInstanceCode,
        array $configuration = []
    ) : JobExecution {
        $jobInstance = $this->get('pim_enrich.repository.job_instance')->findOneBy(['code' => $jobInstanceCode]);

        $job = $this->get('akeneo_batch.job.job_registry')->get($jobInstanceCode);
        $configuration = array_merge($jobInstance->getRawParameters(), $configuration);
        $jobParameters = $this->get('akeneo_batch.job_parameters_factory')->create($job, $configuration);

        return $this->get('akeneo_batch.job_repository')->createJobExecution($jobInstance, $jobParameters);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
