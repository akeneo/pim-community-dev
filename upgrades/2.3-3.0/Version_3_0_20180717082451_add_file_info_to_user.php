<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\Migrations\Exception\IrreversibleMigration;

/**
 * Add file_info_id column to oro_user tables
 */
class Version_3_0_20180717082451_add_file_info_to_user extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->disableMigrationWarning();

        $alterTable = <<<SQL
ALTER TABLE oro_user
ADD COLUMN `file_info_id` int(11) DEFAULT NULL,
ADD CONSTRAINT UNIQUE (`file_info_id`),
ADD CONSTRAINT `FK_F82840BC6ED78C3` FOREIGN KEY (`file_info_id`) REFERENCES `akeneo_file_storage_file_info` (`id`)
SQL;

        $this->addSql($alterTable);
    }

    /**
     * {@inheritdoc}
     *
     * @throws IrreversibleMigration
     */
    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }

    /**
     * Function that does a non altering operation on the DB using SQL to hide the doctrine warning stating that no
     * sql query has been made to the db during the migration process.
     */
    private function disableMigrationWarning()
    {
        $this->addSql('SELECT * FROM oro_user LIMIT 1');
    }
}
