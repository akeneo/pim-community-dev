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
                ('xlsx_product_export', 'Demo XLSX product export', 'xlsx_product_export', 0, 'Akeneo XLSX Connector', 'a:5:{s:10:"withHeader";b:1;s:8:"filePath";s:17:"/tmp/product.xlsx";s:7:"channel";s:6:"mobile";s:16:"decimalSeparator";s:1:".";s:10:"dateFormat";s:10:"yyyy-MM-dd";}', 'export'),
                ('xlsx_product_quick_export', 'XLSX product quick export', 'xlsx_product_quick_export', 0, 'Akeneo Mass Edit Connector', 'a:2:{s:10:"withHeader";b:1;s:8:"filePath";s:53:"/tmp/products_export_%locale%_%scope%_%datetime%.xlsx";}', 'quick_export'),
                ('xlsx_published_product_export', 'Demo XLSX published product export', 'xlsx_published_product_export', 0, 'Akeneo XLSX Connector', 'a:5:{s:10:"withHeader";b:1;s:8:"filePath";s:27:"/tmp/published-product.xlsx";s:7:"channel";s:6:"mobile";s:16:"decimalSeparator";s:1:".";s:10:"dateFormat";s:10:"yyyy-MM-dd";}', 'export'),
                ('xlsx_published_product_quick_export', 'XLSX published product quick export', 'xlsx_published_product_quick_export', 0, 'Akeneo Mass Edit Connector', 'a:2:{s:10:"withHeader";b:1;s:8:"filePath";s:63:"/tmp/published-products_export_%locale%_%scope%_%datetime%.xlsx";}', 'quick_export')
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
