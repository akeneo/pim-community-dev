<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_6_0_20211108135619_create_clean_table_values_following_deleted_options_job extends AbstractMigration
{
    private const JOB_CODE = 'clean_table_values_following_deleted_options';

    public function getDescription() : string
    {
        return sprintf('Create the "%s" job instance', self::JOB_CODE);
    }

    public function up(Schema $schema) : void
    {
        if ($this->jobAlreadyExists()) {
            $this->write('Job already exists');
            $this->disableMigrationWarning();

            return;
        }

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
            SELECT j.id AS job_profile_id, g.id AS user_group_id, 1, 1
            FROM akeneo_batch_job_instance as j
                JOIN oro_access_group AS g ON g.name = 'All'
            WHERE j.code = :code
        ;
SQL;

        $this->addSql($updatePermissionSql, ['code' => static::JOB_CODE]);
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }

    private function jobAlreadyExists(): bool
    {
        $result = $this->connection->executeQuery(
            'SELECT id FROM akeneo_batch_job_instance WHERE code = :code',
            ['code' => self::JOB_CODE]
        )->fetchOne();

        return false !== $result;
    }

    /**
     * Function that does a non altering operation on the DB using SQL to hide the doctrine warning stating that no
     * sql query has been made to the db during the migration process.
     */
    private function disableMigrationWarning(): void
    {
        $this->addSql('SELECT 1');
    }
}
