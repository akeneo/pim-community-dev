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

final class Version_6_0_20220121120000_permission_to_view_mass_edit_and_mass_delete_job_logs_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_6_0_20220121120000_permission_to_view_mass_edit_and_mass_delete_job_logs';

    private const JOB_CODES = [
        'asset_manager_mass_delete_assets',
        'asset_manager_mass_edit_assets',
        'reference_entity_mass_delete_records',
    ];

    public function test_it_add_permissions_to_view_jobs_logs(): void
    {
        $this->removePermissions();

        self::assertFalse($this->permissionExists());

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        self::assertTrue($this->permissionExists());
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
        $this->get('database_connection')
            ->executeQuery(
                $sql,
                ['job_codes' => self::JOB_CODES],
                ['job_codes' => Connection::PARAM_STR_ARRAY]
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
    WHERE access_group.name = 'All' AND job_instance.code IN (:job_codes) AND job_profile_access.execute_job_profile = 1
) AS is_existing
SQL;
        $result = $this->get('database_connection')
            ->executeQuery(
                $sql,
                ['job_codes' => self::JOB_CODES],
                ['job_codes' => Connection::PARAM_STR_ARRAY]
            )
            ->fetchAssociative();

        return 1 === (int) $result['is_existing'];
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
