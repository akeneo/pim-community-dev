<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add the missing background job 'csv_product_quick_export' used by 'CSV export all attributes' in
 * product grid
 */
class Version_1_6_20161028161514_add_missing_csv_product_quick_export extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->write('(re-)create csv_product_quick_export job');
        // remove the job in case it exists somehow (e.g. manual changes of the jobs db or add by console command)
        $this->addSql("DELETE FROM akeneo_batch_job_instance WHERE code = 'csv_product_quick_export';");
        $this->addSql(<<< SQL
            INSERT INTO `akeneo_batch_job_instance`
              (`code`, `label`, `job_name`, `status`, `connector`, `type`, `raw_parameters`)
            VALUES
              (
                'csv_product_quick_export',
                'CSV product quick export',
                'csv_product_quick_export',
                '0',
                'Akeneo Mass Edit Connector',
                'quick_export',
                'a:7:{s:8:\"filePath\";s:52:\"/tmp/products_export_%locale%_%scope%_%datetime%.csv\";s:9:\"delimiter\";s:1:\";\";s:9:\"enclosure\";s:1:\"\"\";s:10:\"withHeader\";b:1;s:7:\"filters\";N;s:19:\"selected_properties\";N;s:10:\"with_media\";b:1;}'
              )
            ;
SQL
        );
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
