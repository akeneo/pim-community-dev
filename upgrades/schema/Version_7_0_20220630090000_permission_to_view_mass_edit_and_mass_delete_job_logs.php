<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add the permission to download logs for the "asset_manager_mass_delete_assets", "asset_manager_mass_edit_assets" and "reference_entity_mass_delete_records" jobs.
 */
final class Version_7_0_20220630090000_permission_to_view_mass_edit_and_mass_delete_job_logs extends AbstractMigration
{
    private const JOB_CODES = [
        'asset_manager_link_assets_to_products',
        'asset_manager_execute_naming_convention',
    ];

    public function up(Schema $schema) : void
    {
        $sql = <<<SQL
INSERT INTO pimee_security_job_profile_access (job_profile_id, user_group_id, execute_job_profile, edit_job_profile)
SELECT ji.id as job_profile_id, oap.id as user_group_id, 1, 0
FROM akeneo_batch_job_instance ji
    CROSS JOIN oro_access_group oap
    LEFT JOIN pimee_security_job_profile_access pa On pa.user_group_id = oap.id AND pa.job_profile_id = ji.id
WHERE oap.name = 'All' AND pa.id IS NULL AND ji.code IN (:jobCodes)
SQL;
        $this->addSql($sql, ['jobCodes' => self::JOB_CODES], ['jobCodes' => Connection::PARAM_STR_ARRAY]);
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }
}
