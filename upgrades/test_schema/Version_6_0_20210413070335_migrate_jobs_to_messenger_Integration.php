<?php
declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;
use Doctrine\DBAL\Connection;
use Google\Cloud\PubSub\Message;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class Version_6_0_20210413070335_migrate_jobs_to_messenger_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_6_0_20210413070335_migrate_jobs_to_messenger';

    private Connection $connection;
    private JobLauncher $jobLauncher;

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
        $this->jobLauncher = $this->get('akeneo_integration_tests.launcher.job_launcher');
        $this->jobLauncher->flushJobQueue();
    }

    public function test_it_migrates_the_non_consumed_jobs()
    {
        $this->recreateQueueTableWithJobs();
        self::assertTrue($this->jobQueueTableExists());
        self::assertCount(0, $this->jobLauncher->getMessagesInQueues());

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        self::assertFalse($this->jobQueueTableExists());
        $messagesInNewQueues = $this->jobLauncher->getMessagesInQueues();
        self::assertCount(2, $messagesInNewQueues);
        $jobExecutionIds = array_map(
            fn (Message $message): int => \json_decode($message->data(), true)['job_execution_id'],
            $messagesInNewQueues
        );
        self::assertCount(2, $jobExecutionIds);
        self::assertContains(2, $jobExecutionIds);
        self::assertContains(3, $jobExecutionIds);
    }

    private function recreateQueueTableWithJobs(): void
    {
        $this->connection->executeQuery(<<<SQL
        create table akeneo_batch_job_execution_queue
        (
            id               int auto_increment primary key,
            job_execution_id int          null,
            options          json         null,
            consumer         varchar(255) null,
            create_time      datetime     null,
            updated_time     datetime     null
        ) collate = utf8mb4_unicode_ci;
        SQL);

        $this->connection->executeQuery(<<<SQL
        INSERT INTO akeneo_batch_job_instance (id, code, job_name, status, connector, raw_parameters, type)
        VALUES (1, 'test', '', 0, '', '', '');
        SQL);

        $this->connection->executeQuery(<<<SQL
        INSERT INTO akeneo_batch_job_execution (id, job_instance_id, create_time, status, raw_parameters) VALUES
        (1, 1, DATE_SUB(NOW(), INTERVAL 10 day), 1, '{}'),
        (2, 1, DATE_SUB(NOW(), INTERVAL 10 day), 1, '{}'),
        (3, 1, DATE_SUB(NOW(), INTERVAL 3 day), 1, '{}');
        SQL);

        $this->connection->executeQuery(<<<SQL
        INSERT INTO akeneo_batch_job_execution_queue (job_execution_id, consumer, create_time, updated_time, options) VALUES 
        (1, 'consumer1', DATE_SUB(NOW(), INTERVAL 10 day), DATE_SUB(NOW(), INTERVAL 9 day), '{}'),
        (2, null, DATE_SUB(NOW(), INTERVAL 10 day), null, '{}'),
        (3, null, DATE_SUB(NOW(), INTERVAL 3 day), null, '{}');
        SQL);
    }

    private function jobQueueTableExists(): bool
    {
        return 1 <= $this->connection->executeQuery("SHOW TABLES LIKE 'akeneo_batch_job_execution_queue';")->rowCount();
    }
}
