<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add "with_media" to "csv_product_quick_export" export job
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
        $query = 'SELECT code, raw_parameters FROM akeneo_batch_job_instance WHERE code = "csv_product_quick_export"';
        $stmt = $this->connection->prepare($query);
        $stmt->execute();
        $job = $stmt->fetch();
        if (null === $job) {
            throw new \Exception('Job "csv_product_quick_export" cannot be found');
        }

        $parameters = unserialize($job['raw_parameters']);
        $parameters['with_media'] = true;

        if (array_key_exists('mainContext', $parameters)) {
            unset($parameters['mainContext']);
        }

        $this->connection->update(
            'akeneo_batch_job_instance',
            ['raw_parameters' => serialize($parameters)],
            ['code'           => 'csv_product_quick_export']
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
