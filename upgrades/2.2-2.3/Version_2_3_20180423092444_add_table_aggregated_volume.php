<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Create the table pim_aggregated_volume for the catalog volume monitoring.
 */
class Version_2_3_20180423092444_add_table_aggregated_volume extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('CREATE TABLE pim_aggregated_volume (volume_name VARCHAR(255) NOT NULL, volume json NOT NULL COMMENT \'(DC2Type:native_json)\', aggregated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime)\', PRIMARY KEY(volume_name)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
