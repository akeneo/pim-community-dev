<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Column;

/**
 * Migrates the user to the version with properties in json instead of override the CE for EE needs
 */
class Version_3_0_20181128084612_migrate_users extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $columns = $this->connection->getSchemaManager()->listTableColumns('oro_user');

        //The email notifications column is now part of the communityEdition
        $column = array_filter($columns, function (Column $column) {
           return $column->getName() === 'emailNotifications';
        });

        if (count($column) === 0) {
            $this->addSql("ALTER TABLE `oro_user` ADD `emailNotifications` tinyint(1) NOT NULL default '0'");
        }

        $this->addSql("ALTER TABLE `oro_user` ADD `properties` longtext NOT NULL comment '(DC2Type:json_array)'");
        $this->addSql("UPDATE oro_user SET properties = '[]'");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
