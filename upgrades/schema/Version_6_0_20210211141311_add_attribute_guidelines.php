<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add the "guidelines" column on the attribute table.
 */
final class Version_6_0_20210211141311_add_attribute_guidelines extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE pim_catalog_attribute ADD guidelines JSON NOT NULL DEFAULT (JSON_OBJECT());');
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }
}
