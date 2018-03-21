<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version_2_2_20180228120422 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $sql = <<<SQL
REPLACE INTO `akeneo_batch_job_instance` (`code`, `label`, `job_name`, `status`, `connector`, `raw_parameters`, `type`)
VALUES
	('add_attribute_value', 'Mass add attribute value', 'add_attribute_value', 0, 'Akeneo Mass Edit Connector', 'a:0:{}', 'mass_edit'),
	('delete_products_and_product_models', 'Mass delete products', 'delete_products_and_product_models', 0, 'Akeneo Mass Edit Connector', 'a:0:{}', 'mass_delete');
SQL;

        $this->addSql($sql);
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
