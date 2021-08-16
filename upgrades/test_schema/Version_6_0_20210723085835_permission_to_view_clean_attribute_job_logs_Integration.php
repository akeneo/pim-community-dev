<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
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
use Pim\Upgrade\Schema\Tests\ExecuteMigrationTrait;

final class Version_6_0_20210723085835_permission_to_view_clean_attribute_job_logs_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_6_0_20210723085835_permission_to_view_clean_attribute_job_logs';
    private Connection $connection;

    public function test_it_add_permissionto_view_job_logs(): void
    {
        $this->connection = $this->get('database_connection');
        $this->removePermission();
        self::assertFalse($this->permissionExists());

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        self::assertTrue($this->permissionExists());
    }

    private function removePermission(): void
    {
        $this->connection->executeQuery(<<<SQL
DELETE a
FROM
    pimee_security_job_profile_access a
        JOIN oro_access_group g ON g.id = a.user_group_id
        JOIN akeneo_batch_job_instance i ON i.id = a.job_profile_id
WHERE g.name = 'All' AND i.code = 'clean_removed_attribute_job'
SQL);
    }

    private function permissionExists(): bool
    {
        $sql = <<<SQL
SELECT EXISTS (
    SELECT a.id
    FROM pimee_security_job_profile_access a
        JOIN oro_access_group g ON g.id = a.user_group_id
        JOIN akeneo_batch_job_instance i ON i.id = a.job_profile_id
    WHERE g.name = 'All' AND i.code = 'clean_removed_attribute_job' AND a.execute_job_profile = 1
) AS is_existing
SQL;
        $result = $this->connection->executeQuery($sql)->fetch(\PDO::FETCH_ASSOC);

        return Type::getType(Types::BOOLEAN)->convertToPhpValue(
            $result['is_existing'],
            $this->connection->getDatabasePlatform()
        );
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
