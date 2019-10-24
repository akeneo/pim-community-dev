<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\BatchQueueBundle\tests\integration\Query;

use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\BatchBundle\Job\DoctrineJobRepository;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\BatchQueue\Queue\JobExecutionMessage;
use Doctrine\DBAL\Driver\Connection;

class SqlDeleteJobExecutionMessageOrphansQueryIntegration extends TestCase
{
    public function testItDeletesJobExecutionMessageOrphans(): void
    {
        $jobInstance = $this->getJobInstanceRepository()->findOneByIdentifier('edit_common_attributes');
        $jobExecution = $this->getJobExecutionRepository()->createJobExecution($jobInstance, new JobParameters([]));

        $this->createJobExecutionMessage($jobExecution->getId());
        $this->createJobExecutionMessage(1234);
        $this->createJobExecutionMessage(9876);

        $this->get('akeneo_batch_queue.query.delete_job_execution_message_orphans')->execute();

        $this->assertTrue($this->jobExecutionMessageExists($jobExecution->getId()));
        $this->assertFalse($this->jobExecutionMessageExists(1234));
        $this->assertFalse($this->jobExecutionMessageExists(9876));
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    /**
     * @return Connection
     */
    private function getDatabaseConnection(): Connection
    {
        return $this->get('doctrine.orm.entity_manager')->getConnection();
    }

    private function getJobExecutionRepository(): DoctrineJobRepository
    {
        return $this->get('akeneo_batch.job_repository');
    }

    private function getJobInstanceRepository(): JobInstanceRepository
    {
        return $this->get('akeneo_batch.job.job_instance_repository');
    }

    private function createJobExecutionMessage(int $jobExecutionId): void
    {
        $jobExecutionMessage = JobExecutionMessage::createJobExecutionMessage($jobExecutionId, []);

        $this->get('akeneo_batch_queue.queue.job_execution_message_repository')->createJobExecutionMessage($jobExecutionMessage);
    }

    private function jobExecutionMessageExists(int $jobExecutionId): bool
    {
        $sql = <<<SQL
SELECT 1
FROM akeneo_batch_job_execution_queue
WHERE job_execution_id = :jobExecutionId
SQL;

        $stmt = $this->getDatabaseConnection()->prepare($sql);
        $stmt->bindValue('jobExecutionId', $jobExecutionId);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }
}
