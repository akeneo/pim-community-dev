<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151109164112 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('RENAME TABLE oro_access_group TO pim_access_group;');
        $this->addSql('RENAME TABLE oro_access_role TO pim_access_role;');
        $this->addSql('RENAME TABLE oro_user TO pim_user;');
        $this->addSql('RENAME TABLE oro_user_access_group TO pim_user_access_group;');
        $this->addSql('RENAME TABLE oro_user_access_group_role TO pim_user_access_group_role;');
        $this->addSql('RENAME TABLE oro_user_access_role TO pim_user_access_role;');
        $this->addSql('RENAME TABLE oro_user_api TO pim_user_api;');

        $this->addSql('DROP INDEX UNIQ_296B6993C912ED9D ON pim_user_api');
        $this->addSql('ALTER TABLE pim_user_api CHANGE user_id user_id INT DEFAULT NULL, CHANGE api_key apiKey VARCHAR(255) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_DEC588AC800A1141 ON pim_user_api (apiKey)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
