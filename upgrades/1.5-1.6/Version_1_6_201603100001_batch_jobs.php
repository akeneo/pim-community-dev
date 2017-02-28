<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Class Version_1_6_201603100001_batch_jobs
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Version_1_6_201603100001_batch_jobs extends AbstractMigration
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
                ('xlsx_published_product_quick_export', 'XLSX published product quick export', 'xlsx_published_product_quick_export', 0, 'Akeneo Mass Edit Connector', 'a:2:{s:10:"withHeader";b:1;s:8:"filePath";s:63:"/tmp/published-products_export_%locale%_%scope%_%datetime%.xlsx";}', 'quick_export')
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
                        'xlsx_published_product_quick_export'
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
