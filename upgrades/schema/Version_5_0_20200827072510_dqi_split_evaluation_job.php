<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version_5_0_20200827072510_dqi_split_evaluation_job extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql(<<<SQL
            INSERT INTO `akeneo_batch_job_instance` (`code`, `label`, `job_name`, `status`, `connector`, `raw_parameters`, `type`)
            VALUES 
           (
                'data_quality_insights_prepare_evaluations',
                'data_quality_insights_prepare_evaluations',
                'data_quality_insights_prepare_evaluations',
                0,
                'Data Quality Insights Connector', 
                'a:0:{}',
                'data_quality_insights'
            );
SQL
        );
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }
}
