<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_5_0_20200623085649_add_indexes_on_product_updated extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql(<<<SQL
            ALTER TABLE pim_catalog_product_model 
                ADD INDEX idx_product_model_updated (updated);
            SQL
        );
        $this->addSql(<<<SQL
            ALTER TABLE pim_catalog_product
                ADD INDEX idx_product_updated (updated);
            SQL
        );
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }
}
