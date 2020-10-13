<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Adds default permissions for the new convert_to_simple_products mass edit job
 */
final class Version_5_0_20201013071343_add_convert_simple_product_job_permissions extends AbstractMigration
{
    private const JOB_NAME = 'convert_to_simple_product';

    public function up(Schema $schema): void
    {
        if ($this->jobProfileAccessIsAlreadyDefined()) {
            return;
        }

        $sql = <<<SQL
        INSERT INTO pimee_security_job_profile_access (job_profile_id, user_group_id, execute_job_profile, edit_job_profile)
            SELECT j.id AS job_profile_id, g.id AS user_group_id, 1, 1
            FROM akeneo_batch_job_instance as j
                JOIN oro_access_group AS g ON g.name = 'All'
            WHERE j.code = :code
        ;
        SQL;

        $this->addSql($sql, ['code' => static::JOB_NAME]);
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }

    private function jobProfileAccessIsAlreadyDefined(): bool
    {
        $sql = <<<SQL
        SELECT EXISTS (
            SELECT a.id
            FROM pimee_security_job_profile_access as a
                JOIN akeneo_batch_job_instance j ON j.id = a.job_profile_id
                JOIN oro_access_group g ON g.id = a.user_group_id
            WHERE j.code = :code AND g.name = 'All'
        ) AS is_existing
        SQL;
        $result = $this->connection->executeQuery($sql, ['code' => static::JOB_NAME])->fetchColumn();

        return (bool) $result;
    }
}
