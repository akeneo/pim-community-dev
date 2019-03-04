<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add an index in the product category access table to improve the performance of the category tree in the product grid.
 */
class Version_3_0_20180716083550_add_index_category_access extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('CREATE INDEX view_category_user_group_idx ON pimee_security_product_category_access (view_items, user_group_id, category_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
