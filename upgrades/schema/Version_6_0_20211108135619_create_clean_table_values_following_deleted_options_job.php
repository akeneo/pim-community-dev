<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version6020211108135619Create extends AbstractMigration
{
    private const JOB_CODE = 'clean_table_values_following_deleted_options';

    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $createJobSql = <<<SQL
        INSERT INTO `akeneo_batch_job_instance` (`code`, `label`, `job_name`, `status`, `connector`, `raw_parameters`, `type`)
        VALUES (
            :job_code, 
            'Remove the non existing table option values from product and product models', 
            :job_name,
            0,
            'internal',
            'a:0:{}', 
            :job_type
        );
        SQL;

        $this->addSql($createJobSql, [
            'job_code' => self::JOB_CODE,
            'job_name' => self::JOB_CODE,
            'job_type' => self::JOB_CODE,
        ]);

        $updatePermissionSql = <<<SQL
        INSERT INTO pimee_security_job_profile_access (job_profile_id, user_group_id, execute_job_profile, edit_job_profile)
        SELECT ji.id as job_profile_id, oap.id as user_group_id, 1, 0
        FROM akeneo_batch_job_instance ji
            CROSS JOIN oro_access_group oap
            LEFT JOIN pimee_security_job_profile_access pa On pa.user_group_id = oap.id AND pa.job_profile_id = ji.id
        WHERE oap.name = 'All' AND pa.id IS NULL AND ji.code = :jobCode
        SQL;
        $this->addSql($updatePermissionSql, ['jobCode' => self::JOB_CODE]);
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }
}
