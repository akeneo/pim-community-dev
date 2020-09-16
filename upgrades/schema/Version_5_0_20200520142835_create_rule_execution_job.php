<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\IrreversibleMigrationException;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_5_0_20200520142835_create_rule_execution_job extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql(<<<SQL
            INSERT INTO akeneo_batch_job_instance (code, label, job_name, status, connector, raw_parameters, type)
            VALUES ('rule_engine_execute_rules', 'Execution of the rules', 'rule_engine_execute_rules', 0, 'Akeneo Rule Engine Connector', 'a:0:{}', 'execute_rules');
            SQL
        );

        $this->addSql(<<<SQL
            INSERT INTO pimee_security_job_profile_access (job_profile_id, user_group_id, execute_job_profile, edit_job_profile)
            SELECT j.id AS job_profile_id, g.id AS user_group_id, 1, 1
            FROM akeneo_batch_job_instance as j
                JOIN oro_access_group AS g ON g.name = 'All'
            WHERE j.code = 'rule_engine_execute_rules';
            SQL
        );
    }

    public function down(Schema $schema) : void
    {
        throw new IrreversibleMigrationException();
    }
}
