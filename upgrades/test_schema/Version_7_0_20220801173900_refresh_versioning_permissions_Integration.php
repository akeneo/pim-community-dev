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
use Pim\Upgrade\Schema\Tests\ExecuteMigrationTrait;

final class Version_7_0_20220801173900_refresh_versioning_permissions_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_7_0_20220801173900_refresh_versioning_permissions';

    private const JOB_CODE = 'versioning_refresh';

    public function test_it_add_permissions_for_the_job(): void
    {
        $this->removePermissions();

        self::assertFalse($this->permissionExists());

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        self::assertTrue($this->permissionExists());
    }

    public function test_it_does_not_add_permissions_for_the_job_if_exists(): void
    {
        self::assertTrue($this->permissionExists());

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        self::assertTrue($this->permissionExists());
    }

    private function removePermissions(): void
    {
        $sql = <<<SQL
            DELETE job_profile_access
            FROM pimee_security_job_profile_access job_profile_access
            JOIN akeneo_batch_job_instance job_instance ON job_instance.id = job_profile_access.job_profile_id
            WHERE job_instance.code = :job_code
        SQL;

        $this->get('database_connection')
            ->executeQuery(
                $sql,
                ['job_code' => self::JOB_CODE],
                ['job_code' => \PDO::PARAM_STR]
            );
    }

    private function permissionExists(): bool
    {
        $sql = <<<SQL
        SELECT EXISTS (
            SELECT job_profile_access.id
            FROM pimee_security_job_profile_access job_profile_access
            JOIN oro_access_group access_group ON access_group.id = job_profile_access.user_group_id
            JOIN akeneo_batch_job_instance job_instance ON job_instance.id = job_profile_access.job_profile_id
            WHERE access_group.name = 'All' AND job_instance.code = :job_code AND job_profile_access.execute_job_profile = 1
        ) AS is_existing
        SQL;

        $result = $this->get('database_connection')
            ->executeQuery(
                $sql,
                ['job_code' => self::JOB_CODE],
                ['job_code' => \PDO::PARAM_STR]
            )
            ->fetchAssociative();

        return 1 === (int)$result['is_existing'];
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
