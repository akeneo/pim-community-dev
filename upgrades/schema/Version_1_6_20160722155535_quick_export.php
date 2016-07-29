<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add "with_media" to "csv_product_quick_export" and "csv_published_product_quick_export" export jobs
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Version_1_6_20160722155535_quick_export extends AbstractMigration
{
    /**
     * @param Schema $schema
     *
     * @throws \Exception
     */
    public function up(Schema $schema)
    {
        $query = 'SELECT code, raw_parameters FROM akeneo_batch_job_instance
                  WHERE code IN ("csv_product_quick_export", "csv_published_product_quick_export")';
        $stmt = $this->connection->prepare($query);
        $stmt->execute();
        $jobs = $stmt->fetchAll();

        foreach ($jobs as $job) {
            $parameters = unserialize($job['raw_parameters']);
            $parameters['with_media'] = true;

            $this->connection->update(
                'akeneo_batch_job_instance',
                ['raw_parameters' => serialize($parameters)],
                ['code' => $job['code']]
            );
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
