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

namespace Akeneo\Tool\Bundle\EnterpriseBatchQueueBundle\tests\integration;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\BatchQueue\Factory\JobExecutionMessageFactory;
use Akeneo\Tool\Component\BatchQueue\Queue\UiJobExecutionMessage;

final class JobExecutionMessageFactoryIntegration extends TestCase
{
    private JobExecutionMessageFactory $jobExecutionMessageFactory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->jobExecutionMessageFactory = $this->get('akeneo_batch_queue.factory.job_execution_message');
    }

    public function test_it_returns_a_ui_job_message_for_an_execute_rules_job(): void
    {
        $jobExecution = $this->createJobExecution('rule_engine_execute_rules');
        $jobExecutionMessage = $this->jobExecutionMessageFactory->buildFromNormalized([
            'id' => 'f8516634-1907-45f0-af75-58eeabba9a32',
            'job_execution_id' => $jobExecution->getId(),
            'consumer' => null,
            'created_time' => '2021-03-08T15:37:23+01:00',
            'updated_time' => null,
            'options' => ['option1' => 'value1'],
        ]);
        self::assertInstanceOf(UiJobExecutionMessage::class, $jobExecutionMessage);
    }

    private function createJobExecution(
        string $jobInstanceCode,
        array $configuration = []
    ) : JobExecution {
        $jobInstance = $this->get('pim_enrich.repository.job_instance')->findOneBy(['code' => $jobInstanceCode]);
        self::assertNotNull($jobInstance);

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
