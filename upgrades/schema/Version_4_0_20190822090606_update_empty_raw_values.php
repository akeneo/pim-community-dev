<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * This migration will update product models with empty raw values as array to empty raw values as
 * associative array.
 */
final class Version_4_0_20190822090606_update_empty_raw_values extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql("UPDATE pim_catalog_product_model SET raw_values = JSON_OBJECT() WHERE raw_values = JSON_ARRAY()");
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }
}
