<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pim\Upgrade\test_schema;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;

final class Version_4_0_20200803082132_remove_non_existing_values_job_permission_Integration extends TestCase
{
    private const JOB_NAME = 'remove_non_existing_product_values';

    /** @var Connection */
    private $connection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = $this->get('database_connection');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_adds_job_profile_access_for_job()
    {
        $this->removeJobProfileAccess();
        $this->assertFalse($this->jobProfileAccessIsDefined());

        $this->runMigration();
        $this->assertTrue($this->jobProfileAccessIsDefined());

        # Run again to check the migration is idempotent
        $this->runMigration();
        $this->assertTrue($this->jobProfileAccessIsDefined());
    }

    private function runMigration(): void
    {
        $migrationCommand = sprintf('doctrine:migrations:execute %s --up -n', $this->getMigrationLabel());
        $this->get('pim_catalog.command_launcher')->executeForeground($migrationCommand);
    }

    private function getMigrationLabel()
    {
        $migration = (new \ReflectionClass($this))->getShortName();
        $migration = str_replace('_Integration', '', $migration);
        $migration = str_replace('Version', '', $migration);

        return $migration;
    }

    private function removeJobProfileAccess()
    {
        $sql = <<<SQL
DELETE access
FROM pimee_security_job_profile_access access
    JOIN akeneo_batch_job_instance instance ON instance.id = access.job_profile_id
WHERE instance.code = :code;
SQL;

        $this->connection->executeQuery($sql, ['code' => static::JOB_NAME]);
    }

    private function jobProfileAccessIsDefined(): bool
    {
        $sql = <<<SQL
SELECT EXISTS (
    SELECT a.id
    FROM pimee_security_job_profile_access as a
        JOIN akeneo_batch_job_instance j ON j.id = a.job_profile_id
        JOIN oro_access_group g ON g.id = a.user_group_id
    WHERE j.code = :code AND g.name = 'All'
) AS is_existing
SQL;
        $result = $this->connection->executeQuery($sql, ['code' => static::JOB_NAME])->fetch(\PDO::FETCH_ASSOC);

        return Type::getType(Types::BOOLEAN)->convertToPhpValue(
            $result['is_existing'],
            $this->connection->getDatabasePlatform()
        );
    }
}
