<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add
 *
 * @author Philippe Mossière <philippe.mossiere@akeneo.com>
 */
class Version_1_6_20160729114314_batch_jobs extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql(<<<SQL
            INSERT INTO akeneo_batch_job_instance
                (`code`, `label`, `job_name`, `status`, `connector`, `raw_parameters`, `type`)
            VALUES
                ('csv_published_product_grid_context_quick_export', 'CSV published product quick export grid context', 'csv_published_product_grid_context_quick_export', 0, 'Akeneo CSV Connector', 'a:7:{s:8:"filePath";s:75:"/tmp/published_products_export_grid_context_%locale%_%scope%_%datetime%.csv";s:9:"delimiter";s:1:";";s:9:"enclosure";s:1:""";s:10:"withHeader";b:1;s:7:"filters";N;s:11:"mainContext";N;s:19:"selected_properties";N;}', 'quick_export'),
                ('xlsx_published_product_grid_context_quick_export', 'XLSX published product quick export grid context', 'xlsx_published_product_grid_context_quick_export', 0, 'Akeneo XLSX Connector', 'a:6:{s:8:"filePath";s:76:"/tmp/published_products_export_grid_context_%locale%_%scope%_%datetime%.xlsx";s:10:"withHeader";b:1;s:12:"linesPerFile";i:10000;s:7:"filters";N;s:11:"mainContext";N;s:19:"selected_properties";N;}', 'quick_export')
            ;
SQL
        );

        $this->addSql(<<<SQL
            INSERT INTO pimee_security_job_profile_access
                (`job_profile_id`, `user_group_id`, `execute_job_profile`, `edit_job_profile`)
                    SELECT j.id AS job_profile_id, g.id AS user_group_id, 1, 1
                    FROM akeneo_batch_job_instance as j
                    JOIN oro_access_group AS g ON g.name = "All"
                    WHERE j.code IN (
                        'csv_published_product_grid_context_quick_export',
                        'xlsx_published_product_grid_context_quick_export'
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
