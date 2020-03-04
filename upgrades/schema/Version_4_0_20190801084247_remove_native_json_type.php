<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_4_0_20190801084247_remove_native_json_type extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql("ALTER TABLE pim_catalog_product MODIFY raw_values JSON NOT NULL COMMENT ''");
        $this->addSql("ALTER TABLE pim_catalog_product_model MODIFY raw_values JSON NOT NULL COMMENT ''");
        $this->addSql("ALTER TABLE pim_aggregated_volume MODIFY volume JSON NOT NULL COMMENT ''");
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }
}
