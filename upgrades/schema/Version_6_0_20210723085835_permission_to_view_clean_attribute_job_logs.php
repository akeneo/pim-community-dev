<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add the permission to download logs for the "clean_removed_attribute_job" job.
 */
final class Version_6_0_20210723085835_permission_to_view_clean_attribute_job_logs extends AbstractMigration
{
    private const JOB_CODE = 'clean_removed_attribute_job';

    public function up(Schema $schema) : void
    {
        $sql = <<<SQL
INSERT INTO pimee_security_job_profile_access (job_profile_id, user_group_id, execute_job_profile, edit_job_profile)
SELECT ji.id as job_profile_id, oap.id as user_group_id, 1, 0
FROM akeneo_batch_job_instance ji
    CROSS JOIN oro_access_group oap
    LEFT JOIN pimee_security_job_profile_access pa On pa.user_group_id = oap.id AND pa.job_profile_id = ji.id
WHERE oap.name = 'All' AND pa.id IS NULL AND ji.code = :jobCode
SQL;
        $this->addSql($sql, ['jobCode' => self::JOB_CODE]);
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }
}
