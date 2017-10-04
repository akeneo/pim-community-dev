<?php

declare(strict_types=1);

namespace Akeneo\Bundle\BatchQueueBundle\tests\integration\Launcher;

use Akeneo\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Component\Batch\Job\BatchStatus;
use Akeneo\Component\Batch\Job\ExitStatus;
use Akeneo\Component\BatchQueue\Queue\JobExecutionMessage;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Driver\Connection;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class QueueJobLauncherIntegration extends TestCase
{
    /** @var JobLauncher */
    protected $jobLauncher;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->jobLauncher = new JobLauncher(static::$kernel);
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

        $connection = $this->getConnection();
        $stmt = $connection->prepare('SELECT * from akeneo_batch_job_execution_queue');
        $stmt->execute();
        $row = $stmt->fetch();

        $this->assertEquals(1, $row['id']);
        $this->assertEquals('{"env":"test","email":"mary@example.com"}', $row['options']);
        $this->assertNotNull($row['create_time']);
        $this->assertNull($row['updated_time']);
        $this->assertNull($row['consumer']);

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

    /**
     * @return JobLauncherInterface
     */
    protected function getJobLauncher(): JobLauncherInterface
    {
        return $this->get('akeneo_batch_queue.launcher.queue_job_launcher');
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
