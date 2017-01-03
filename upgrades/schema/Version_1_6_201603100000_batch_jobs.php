<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Class Version_1_6_201603100000_batch_jobs
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Version_1_6_201603100000_batch_jobs extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql(<<<SQL
            INSERT INTO akeneo_batch_job_instance
                (`code`, `label`, `alias`, `status`, `connector`, `rawConfiguration`, `type`)
            VALUES
			    ('csv_product_grid_context_quick_export', 'CSV product quick export grid context', 'csv_product_grid_context_quick_export', 0,'Akeneo Mass Edit Connector', 'a:7:{s:8:"filePath";s:65:"/tmp/products_export_grid_context_%locale%_%scope%_%datetime%.csv";s:9:"delimiter";s:1:";";s:9:"enclosure";s:1:""";s:10:"withHeader";b:1;s:7:"filters";N;s:19:"selected_properties";N;s:10:"with_media";b:1;}', 'quick_export'),
			    ('xlsx_product_quick_export', 'XLSX product quick export', 'xlsx_product_quick_export', 0,'Akeneo Mass Edit Connector', 'a:6:{s:8:"filePath";s:53:"/tmp/products_export_%locale%_%scope%_%datetime%.xlsx";s:10:"withHeader";b:1;s:12:"linesPerFile";i:10000;s:7:"filters";N;s:19:"selected_properties";N;s:10:"with_media";b:1;}', 'quick_export'),
			    ('xlsx_product_grid_context_quick_export', 'XLSX product quick export grid context', 'xlsx_product_grid_context_quick_export', 0,'Akeneo Mass Edit Connector', 'a:6:{s:8:"filePath";s:66:"/tmp/products_export_grid_context_%locale%_%scope%_%datetime%.xlsx";s:10:"withHeader";b:1;s:12:"linesPerFile";i:10000;s:7:"filters";N;s:19:"selected_properties";N;s:10:"with_media";b:1;}', 'quick_export')
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
