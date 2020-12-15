<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version_2_3_20180620134351_add_table_structure_version_last_update extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE akeneo_structure_version_last_update (resource_name varchar(255) COLLATE utf8_unicode_ci NOT NULL, last_update datetime NOT NULL COMMENT \'(DC2Type:datetime)\', PRIMARY KEY (resource_name)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
