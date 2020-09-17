<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pimee\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class Version_4_0_20191216123608_compute_transformations_job_permission_Integration extends TestCase
{
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

    /** @test */
    public function it_adds_job_profile_access_for_job()
    {
        $this->removeJobProfileAccess();
        $this->assertFalse($this->jobProfileAccessIsDefined());

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
WHERE instance.code = 'asset_manager_compute_transformations';
SQL;

        $this->connection->executeQuery($sql);
    }

    private function jobProfileAccessIsDefined(): bool
    {
        $sql = <<<SQL
SELECT EXISTS (
    SELECT a.id
    FROM pimee_security_job_profile_access as a
        JOIN akeneo_batch_job_instance j ON j.id = a.job_profile_id
        JOIN oro_access_group g ON g.id = a.user_group_id
    WHERE j.code = 'asset_manager_compute_transformations' AND g.name = 'All'
) AS is_existing
SQL;
        $result = $this->connection->executeQuery($sql)->fetch(\PDO::FETCH_ASSOC);

        return Type::getType(Types::BOOLEAN)->convertToPhpValue(
            $result['is_existing'],
            $this->connection->getDatabasePlatform()
        );
    }
}
