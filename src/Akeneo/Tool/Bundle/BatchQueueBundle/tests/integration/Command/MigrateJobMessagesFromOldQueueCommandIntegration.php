<?php
declare(strict_types=1);

namespace Akeneo\Tool\Bundle\BatchQueueBundle\tests\integration\Command;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Launcher\JobLauncher;
use Akeneo\Tool\Bundle\BatchBundle\Command\BatchCommand;
use Doctrine\DBAL\Connection;
use Google\Cloud\PubSub\Message;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class MigrateJobMessagesFromOldQueueCommandIntegration extends TestCase
{
    private Connection $connection;
    private JobLauncher $jobLauncher;

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    /** @test */
    public function it_migrates_all_the_jobs(): void
    {
        self::assertCount(0, $this->jobLauncher->getMessagesInQueues());
        self::assertGreaterThan(0, $this->getMessageCountInOldQueue());

        $this->runMigrationCommand();

        $messagesInNewQueues = $this->jobLauncher->getMessagesInQueues();
        self::assertCount(2, $messagesInNewQueues);
        $jobExecutionIds = array_map(
            fn (Message $message): int => \json_decode($message->data(), true)['job_execution_id'],
            $messagesInNewQueues
        );
        self::assertCount(2, $jobExecutionIds);
        self::assertContains(2, $jobExecutionIds);
        self::assertContains(3, $jobExecutionIds);
        self::assertSame(0, $this->getMessageCountInOldQueue());
    }

    /** @test */
    public function it_migrates_the_jobs_using_limit(): void
    {
        self::assertCount(0, $this->jobLauncher->getMessagesInQueues());
        self::assertSame(2, $this->getMessageCountInOldQueue());

        $this->runMigrationCommand(1);
        self::assertCount(1, $this->jobLauncher->getMessagesInQueues());
        self::assertSame(1, $this->getMessageCountInOldQueue());

        $this->runMigrationCommand(1);
        self::assertCount(2, $this->jobLauncher->getMessagesInQueues());
        self::assertSame(0, $this->getMessageCountInOldQueue());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = $this->get('database_connection');
        $this->jobLauncher = $this->get('akeneo_integration_tests.launcher.job_launcher');

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

    private function runMigrationCommand(int $limit = 0): void
    {
        $application = new Application($this->get('kernel'));
        $application->setAutoExit(false);

        $input = new ArrayInput([
            'command'  => 'akeneo:batch:migrate-job-messages-from-old-queue',
            '--limit' => $limit,
            '--no-interaction' => true,
            '-v' => true,
        ]);

        $output = new BufferedOutput();
        $exitCode = $application->run($input, $output);

        self::assertSame(BatchCommand::EXIT_SUCCESS_CODE, $exitCode, sprintf('Export failed, "%s".', $output->fetch()));
    }

    private function getMessageCountInOldQueue(): int
    {
        $query = 'SELECT count(id) FROM akeneo_batch_job_execution_queue WHERE consumer IS NULL';

        return (int) $this->connection->executeQuery($query)->fetchColumn();
    }
}
