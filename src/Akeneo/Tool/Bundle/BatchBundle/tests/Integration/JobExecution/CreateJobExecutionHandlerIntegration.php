<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\BatchBundle\tests\Integration\JobExecution;

use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\BatchBundle\JobExecution\CreateJobExecutionHandlerInterface;
use Akeneo\Tool\Component\Batch\Model\JobInstance;

class CreateJobExecutionHandlerIntegration extends TestCase
{
    public function test_create_job_execution_with_job_parameters()
    {
        $this->createJobInstance('stoppable_dumb_job');

        $handler = $this->getCreateJobExecutionHandler();
        $jobExecution = $handler->createFromBatchCode(
            'stoppable_dumb_job_instance',
            [],
            null
        );

        $result = $this->selectJobExecution($jobExecution->getId());

        $expectedResult = [
            'status' => '2',
            'exit_code' => 'UNKNOWN',
            'raw_parameters' => '[]',
            'is_stoppable' => '1',
            'is_visible' => '1',
        ];

        $this->assertEquals($expectedResult['status'], $result['status']);
        $this->assertEquals($expectedResult['exit_code'], $result['exit_code']);
        $this->assertJsonStringEqualsJsonString($expectedResult['raw_parameters'], $result['raw_parameters']);
        $this->assertEquals($expectedResult['is_stoppable'], $result['is_stoppable']);
        $this->assertEquals($expectedResult['is_visible'], $result['is_visible']);
    }

    private function getCreateJobExecutionHandler(): CreateJobExecutionHandlerInterface
    {
        return $this->get(CreateJobExecutionHandlerInterface::class);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function selectJobExecution(int $id): array
    {
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
}
