<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\BatchQueueBundle\tests\integration\Queue;

use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\BatchQueue\Queue\JobExecutionMessage;
use Akeneo\Tool\Component\BatchQueue\Queue\JobExecutionQueueInterface;
use Doctrine\DBAL\Driver\Connection;

class DatabaseJobExecutionQueueIntegration extends TestCase
{
    public function testPublishAJobExecutionMessage()
    {
        $jobExecutionMessage = JobExecutionMessage::createJobExecutionMessage(1, ['email' => 'ziggy@akeneo.com']);

        $this->getQueue()->publish($jobExecutionMessage);

        $connection = $this->getConnection();
        $stmt = $connection->prepare('SELECT * from akeneo_batch_job_execution_queue');
        $stmt->execute();
        $row = $stmt->fetch();

        $this->assertEquals(1, $row['job_execution_id']);
        $this->assertJsonStringEqualsJsonString('{"email": "ziggy@akeneo.com"}', $row['options']);
        $this->assertNotNull($row['create_time']);
        $this->assertNull($row['updated_time']);
        $this->assertNull($row['consumer']);
    }

    public function testConsumeAJobExecutionMessage()
    {
        $sql = <<<SQL
INSERT INTO akeneo_batch_job_execution_queue 
    (job_execution_id, options, create_time, updated_time, consumer)
VALUES 
    (1, "{\"email\": \"ziggy_1@akeneo.com\"}", "2017-08-30 10:15:30", null, null),
    (2, "{\"email\": \"ziggy_2@akeneo.com\"}", "2017-08-30 10:00:00", null, "consumer_name")
SQL;

        $connection = $this->getConnection();
        $stmt = $connection->prepare($sql);
        $stmt->execute();

        $jobExecutionMessage = $this->getQueue()->consume('consumer_name');
        $this->assertEquals(1, $jobExecutionMessage->getJobExecutionId());
        $this->assertEquals(['email' => 'ziggy_1@akeneo.com'], $jobExecutionMessage->getOptions());
        $this->assertNull($jobExecutionMessage->getUpdatedTime());
        $this->assertEquals('consumer_name', $jobExecutionMessage->getConsumer());

        $stmt = $connection->prepare('SELECT * from akeneo_batch_job_execution_queue q where q.job_execution_id = 1');
        $stmt->execute();
        $row = $stmt->fetch();

        $this->assertEquals(1, $row['job_execution_id']);
        $this->assertJsonStringEqualsJsonString('{"email": "ziggy_1@akeneo.com"}', $row['options']);
        $this->assertEquals('2017-08-30 10:15:30', $row['create_time']);
        $this->assertEquals($jobExecutionMessage->getConsumer(), $row['consumer']);
        $this->assertNotNull($row['updated_time']);
    }

    public function testConsumeTheOldestJobExecutionMessage()
    {
        $sql = <<<SQL
INSERT INTO akeneo_batch_job_execution_queue 
    (job_execution_id, options, create_time, updated_time, consumer)
VALUES 
    (1, "{\"email\":\"ziggy_1@akeneo.com\"}", "2017-08-30 10:15:30", null, null),
    (2, "{\"email\":\"ziggy_2@akeneo.com\"}", "2017-08-30 10:10:30", null, null),
    (3, "{\"email\":\"ziggy_3@akeneo.com\"}", "2017-08-30 10:00:00", "2017-08-30 10:00:05", "consumer_name")
SQL;

        $connection = $this->getConnection();
        $stmt = $connection->prepare($sql);
        $stmt->execute();

        $jobExecutionMessage = $this->getQueue()->consume('consumer_name');
        $this->assertEquals(2, $jobExecutionMessage->getJobExecutionId());
        $this->assertEquals(['email' => 'ziggy_2@akeneo.com'], $jobExecutionMessage->getOptions());
        $this->assertNull($jobExecutionMessage->getUpdatedTime());
        $this->assertEquals('consumer_name', $jobExecutionMessage->getConsumer());

        $stmt = $connection->prepare('SELECT * from akeneo_batch_job_execution_queue q where q.job_execution_id = 2');
        $stmt->execute();
        $row = $stmt->fetch();

        $this->assertEquals(2, $row['job_execution_id']);
        $this->assertJsonStringEqualsJsonString('{"email": "ziggy_2@akeneo.com"}', $row['options']);
        $this->assertEquals('2017-08-30 10:10:30', $row['create_time']);
        $this->assertEquals($jobExecutionMessage->getConsumer(), $row['consumer']);
        $this->assertNotNull($row['updated_time']);

        $jobExecutionMessage = $this->getQueue()->consume('consumer_name');
        $this->assertEquals(1, $jobExecutionMessage->getJobExecutionId());
    }

    /**
     * @return JobExecutionQueueInterface
     */
    protected function getQueue(): JobExecutionQueueInterface
    {
        return $this->get('akeneo_batch_queue.queue.database_job_execution_queue');
    }

    /**
     * @return Connection
     */
    protected function getConnection(): Connection
    {
        return $this->get('doctrine.orm.entity_manager')->getConnection();
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
