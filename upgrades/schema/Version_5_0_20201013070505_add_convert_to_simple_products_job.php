<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_5_0_20201013070505_add_convert_to_simple_products_job extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $sql = <<<SQL
        REPLACE INTO `akeneo_batch_job_instance` (`code`, `label`, `job_name`, `status`, `connector`, `raw_parameters`, `type`)
        VALUES ('convert_to_simple_products', 'Convert to simple products', 'convert_to_simple_products', 0, 'Akeneo Mass Edit Connector', 'a:0:{}', 'mass_edit');
        SQL;

        $this->addSql($sql);
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }
}
