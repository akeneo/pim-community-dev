<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;
use Symfony\Component\Process\PhpExecutableFinder;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Version_6_0_20220518130906_drop_table_akeneo_batch_job_execution_queue_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_6_0_20220518130906_drop_table_akeneo_batch_job_execution_queue';

    private Connection $connection;

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
    }

    public function test_it_adds_new_index_on_job_execution_table(): void
    {
        $this->createJobQueueTable();

        Assert::assertTrue($this->jobQueueTableExists());
        $this->reExecuteMigration(self::MIGRATION_LABEL);
        Assert::assertFalse($this->jobQueueTableExists());
    }

    public function test_migration_is_idempotent(): void
    {
        $this->createJobQueueTable();

        $this->reExecuteMigration(self::MIGRATION_LABEL);
        $this->reExecuteMigration(self::MIGRATION_LABEL);

        Assert::assertFalse($this->jobQueueTableExists());
    }

    public function test_it_throw_an_error_if_job_queue_is_not_empty(): void
    {
        $this->createJobQueueTable();
        $this->queueJob();

        $this->reExecuteMigrationWithExpectedError(self::MIGRATION_LABEL);

        Assert::assertTrue($this->jobQueueTableExists());
    }

    private function createJobQueueTable(): void
    {
        if ($this->jobQueueTableExists()) {
            return;
        }

        $this->get('database_connection')->executeQuery(<<<SQL
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
    }

    private function jobQueueTableExists(): bool
    {
        return 1 <= $this
                ->connection
                ->executeQuery("SHOW TABLES LIKE 'akeneo_batch_job_execution_queue';")
                ->rowCount();
    }

    private function queueJob(): void
    {
        $this->connection->executeQuery(<<<SQL
            INSERT INTO akeneo_batch_job_execution_queue (job_execution_id, consumer, create_time, updated_time, options) VALUES 
            (1, 'consumer1', DATE_SUB(NOW(), INTERVAL 10 day), DATE_SUB(NOW(), INTERVAL 9 day), '{}'),
            (2, null, DATE_SUB(NOW(), INTERVAL 10 day), null, '{}'),
            (3, null, DATE_SUB(NOW(), INTERVAL 3 day), null, '{}');
        SQL);
    }

    private function reExecuteMigrationWithExpectedError(string $migrationLabel): void
    {
        $pathFinder = new PhpExecutableFinder();
        $phpCommand = $pathFinder->find();
        $rootDir = $this->getParameter('kernel.project_dir');

        $output = [];
        $status = null;

        exec(
            sprintf(
                "%s %s/bin/console doctrine:migrations:execute 'Pim\Upgrade\Schema\Version%s' --down -n 2>&1",
                $phpCommand,
                $rootDir,
                $migrationLabel
            ),
            $output,
            $status
        );

        Assert::assertEquals(1, $status, 'Migration should be irreversible.');

        exec(
            sprintf(
                "%s %s/bin/console doctrine:migrations:execute 'Pim\Upgrade\Schema\Version%s' --up -n  2>&1",
                $phpCommand,
                $rootDir,
                $migrationLabel
            ),
            $output,
            $status
        );
        Assert::assertEquals(1, $status, \json_encode($output));
    }
}
