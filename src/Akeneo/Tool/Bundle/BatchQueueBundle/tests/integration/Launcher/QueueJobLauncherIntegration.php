<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\BatchQueueBundle\tests\integration\Launcher;

use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Akeneo\Tool\Component\Batch\Job\ExitStatus;
use Doctrine\DBAL\Driver\Connection;
use Google\Cloud\PubSub\Message;

class QueueJobLauncherIntegration extends TestCase
{
    protected JobLauncher $jobLauncher;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->jobLauncher = $this->get('akeneo_integration_tests.launcher.job_launcher');
        $this->jobLauncher->flushJobQueue();
    }

    public function testPublishAndRunAJobExecutionMessageIntoTheQueue()
    {
        $jobInstanceClass = $this->getParameter('akeneo_batch.entity.job_instance.class');
        $jobInstance = $this
            ->get('doctrine.orm.default_entity_manager')
            ->getRepository($jobInstanceClass)
            ->findOneBy(['code' => 'csv_product_export']);

        $user = $this->get('pim_user.provider.user')->loadUserByUsername('mary');

        $this->getJobLauncher()->launch($jobInstance, $user, ['send_email' => true]);

        $messages = $this->getJobMessagesInQueues();
        self::assertCount(1, $messages);
        /** @var Message $message */
        $message = $messages[0];
        $data = \json_decode($message->data(), true);
        self::assertSame(['env' => 'test', 'email' => ['mary@example.com']], $data['options']);
        self::assertNotNull($data['created_time']);
        self::assertNull($data['updated_time']);

        $connection = $this->getConnection();
        $stmt = $connection->prepare('SELECT user, status, exit_code, health_check_time from akeneo_batch_job_execution');
        $stmt->execute();
        $row = $stmt->fetch();

        $this->assertEquals('mary', $row['user']);
        $this->assertEquals(BatchStatus::STARTING, $row['status']);
        $this->assertEquals(ExitStatus::UNKNOWN, $row['exit_code']);
        $this->assertNull($row['health_check_time']);

        $this->jobLauncher->launchConsumerOnce();

        $stmt = $connection->prepare('SELECT user, status, exit_code, health_check_time from akeneo_batch_job_execution');
        $stmt->execute();
        $row = $stmt->fetch();

        $this->assertEquals('mary', $row['user']);
        $this->assertEquals(BatchStatus::COMPLETED, $row['status']);
        $this->assertEquals(ExitStatus::COMPLETED, $row['exit_code']);
        $this->assertNotNull($row['health_check_time']);
    }

    protected function getJobLauncher(): JobLauncherInterface
    {
        return $this->get('akeneo_batch_queue.launcher.queue_job_launcher');
    }

    protected function getConnection(): Connection
    {
        return $this->get('doctrine.orm.entity_manager')->getConnection();
    }

    protected function getJobMessagesInQueues(): array
    {
        return array_merge(
            $this->get('akeneo_integration_tests.pub_sub_queue_status.ui_job')->getMessagesInQueue(),
            $this->get('akeneo_integration_tests.pub_sub_queue_status.import_export_job')->getMessagesInQueue(),
            $this->get('akeneo_integration_tests.pub_sub_queue_status.data_maintenance_job')->getMessagesInQueue(),
            $this->get('akeneo_integration_tests.pub_sub_queue_status.scheduled_job')->getMessagesInQueue(),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
