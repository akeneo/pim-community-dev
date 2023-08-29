<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Version_8_0_20230817162126_add_purge_orphan_category_image_files_job_instance_Integration extends TestCase
{
    private const MIGRATION_NAME = '_8_0_20230817162126_add_purge_orphan_category_image_files_job_instance';

    use ExecuteMigrationTrait;

    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
    }

    public function test_it_does_nothing_if_the_job_already_exists(): void
    {
        Assert::assertTrue($this->jobInstanceExists());
        $this->reExecuteMigration(self::MIGRATION_NAME);
        Assert::assertTrue($this->jobInstanceExists());
    }

    public function test_it_adds_the_job_instance(): void
    {
        $this->connection->executeStatement(
            <<<SQL
            DELETE FROM akeneo_batch_job_instance WHERE code = 'purge_orphan_category_image_files';
            SQL
        );
        Assert::assertFalse($this->jobInstanceExists());
        $this->reExecuteMigration(self::MIGRATION_NAME);
        Assert::assertTrue($this->jobInstanceExists());
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function jobInstanceExists(): bool
    {
        return (bool) $this->connection->fetchOne(
            <<<SQL
            SELECT EXISTS(
                SELECT * FROM akeneo_batch_job_instance WHERE code = 'purge_orphan_category_image_files'
            );
            SQL
        );
    }
}
