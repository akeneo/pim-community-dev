<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add an index in the category table to improve the performance of the category tree in the product grid.
 */
class Version_3_0_20180716083150_add_index_category extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX left_idx ON pim_catalog_category (lft)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
