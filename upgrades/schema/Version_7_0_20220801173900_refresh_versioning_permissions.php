<?php
/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add the permission for the "refresh_versioning" job.
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
final class Version_7_0_20220801173900_refresh_versioning_permissions extends AbstractMigration
{
    private const JOB_CODE = 'versioning_refresh';

    public function up(Schema $schema): void
    {
        $sql = <<<SQL
            insert into pimee_security_job_profile_access 
                (job_profile_id, user_group_id, execute_job_profile, edit_job_profile) 
            with 
                 default_user_group_and_app as ( select id as user_group_id from oro_access_group where name ='All' ), 
                 job_instance as ( select id as job_profile_id from akeneo_batch_job_instance where code = :job_code ) 
            select job_instance.job_profile_id, 
                   default_user_group_and_app.user_group_id, 
                   1 as execute_job_profile, 
                   1 as edit_job_profile 
            from default_user_group_and_app, job_instance 
            on duplicate key 
                update pimee_security_job_profile_access.job_profile_id = pimee_security_job_profile_access.job_profile_id; ;            
        SQL;

        $this->addSql(
            $sql,
            ['job_code' => self::JOB_CODE],
            ['job_code' => \PDO::PARAM_STR]
        );
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
