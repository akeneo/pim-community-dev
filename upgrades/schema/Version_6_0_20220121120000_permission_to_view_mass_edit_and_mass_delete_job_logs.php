<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add the permission to download logs for the "asset_manager_mass_delete_assets", "asset_manager_mass_edit_assets" and "reference_entity_mass_delete_records" jobs.
 */
final class Version_6_0_20220121120000_permission_to_view_mass_edit_and_mass_delete_job_logs extends AbstractMigration
{
    private const JOB_CODES = [
        'asset_manager_mass_delete_assets',
        'asset_manager_mass_edit_assets',
        'reference_entity_mass_delete_records',
    ];

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
        $result = $this->connection
            ->executeQuery(
                $sql,
                ['job_codes' => self::JOB_CODES],
                ['job_codes' => Connection::PARAM_STR_ARRAY]
            )
            ->fetchAssociative();

        return 1 === (int) $result['is_existing'];
    }

    public function up(Schema $schema) : void
    {
        $sql = <<<SQL
INSERT INTO pimee_security_job_profile_access (job_profile_id, user_group_id, execute_job_profile, edit_job_profile)
SELECT job_instance.id as job_profile_id, access_group.id as user_group_id, 1, 0
FROM akeneo_batch_job_instance job_instance
CROSS JOIN oro_access_group access_group
LEFT JOIN pimee_security_job_profile_access job_profile_access ON job_profile_access.user_group_id = access_group.id AND job_profile_access.job_profile_id = job_instance.id
WHERE access_group.name = 'All' AND job_profile_access.id IS NULL AND job_instance.code IN (:job_codes)
SQL;

        if (!$this->permissionExists()) {
            $this->addSql(
                $sql,
                ['job_codes' => self::JOB_CODES],
                ['job_codes' => Connection::PARAM_STR_ARRAY]
            );
        }
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }
}
