<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_5_0_20200817123559_remove_franklin_insights extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql(<<<SQL
DROP TABLE IF EXISTS pimee_franklin_insights_attribute_added_to_family;
DROP TABLE IF EXISTS pimee_franklin_insights_attribute_created;
DROP TABLE IF EXISTS pimee_franklin_insights_identifier_mapping;
DROP TABLE IF EXISTS pimee_franklin_insights_quality_highlights_pending_items;
DROP TABLE IF EXISTS pimee_franklin_insights_subscription;
SQL
        );

        $this->addSql(<<<SQL
DELETE job_queue
FROM akeneo_batch_job_instance AS job_instance
LEFT JOIN akeneo_batch_job_execution AS job_execution ON job_execution.job_instance_id = job_instance.id
LEFT JOIN akeneo_batch_job_execution_queue AS job_queue ON job_queue.job_execution_id = job_execution.id
WHERE job_instance.code LIKE 'franklin_insights_%';
SQL
        );

        $this->addSql(<<<SQL
DELETE FROM akeneo_batch_job_instance WHERE code LIKE 'franklin_insights_%';
SQL
        );

        $this->addSql(<<<SQL
DELETE IGNORE FROM oro_user
WHERE username = 'Franklin' AND first_name = 'Franklin' AND last_name = 'Insights' AND email = 'admin@akeneo.com'
SQL
        );
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }
}
