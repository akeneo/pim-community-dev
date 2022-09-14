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

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use Pim\Upgrade\Schema\Tests\ExecuteMigrationTrait;

final class Version_7_0_20220630090000_permission_to_view_mass_edit_and_mass_delete_job_logs_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_7_0_20220630090000_permission_to_view_mass_edit_and_mass_delete_job_logs';

    private const JOB_CODES = [
        'asset_manager_link_assets_to_products',
        'asset_manager_execute_naming_convention',
    ];

    public function test_it_add_permissions_to_view_jobs_logs(): void
    {
        $this->removePermissions();

        foreach (self::JOB_CODES as $jobCode) {
            self::assertFalse($this->permissionExists($jobCode));
        }

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        foreach (self::JOB_CODES as $jobCode) {
            self::assertTrue($this->permissionExists($jobCode));
        }
    }

    private function removePermissions(): void
    {
        $sql = <<<SQL
DELETE job_profile_access
FROM pimee_security_job_profile_access job_profile_access
JOIN oro_access_group access_group ON access_group.id = job_profile_access.user_group_id
JOIN akeneo_batch_job_instance job_instance ON job_instance.id = job_profile_access.job_profile_id
WHERE access_group.name = 'All' AND job_instance.code IN (:job_codes)
SQL;
        $this->get('database_connection')->executeQuery($sql, ['job_codes' => self::JOB_CODES], ['job_codes' => Connection::PARAM_STR_ARRAY]);
    }

    private function permissionExists(string $jobCode): bool
    {
        $sql = <<<SQL
SELECT EXISTS (
    SELECT a.id
    FROM pimee_security_job_profile_access a
        JOIN oro_access_group g ON g.id = a.user_group_id
        JOIN akeneo_batch_job_instance i ON i.id = a.job_profile_id
    WHERE g.name = 'All' AND i.code = :jobCode AND a.execute_job_profile = 1
) AS is_existing
SQL;
        $result = $this->get('database_connection')->executeQuery($sql, ['jobCode' => $jobCode], ['jobCodes' => Connection::PARAM_STR_ARRAY])->fetchAssociative();

        return 1 === (int) $result['is_existing'];
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
