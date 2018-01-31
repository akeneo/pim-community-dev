<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add tables needed for OAuth2 authentication.
 */
class Version_1_7_20170112144222_oauth extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('CREATE TABLE pim_api_client (id INT AUTO_INCREMENT NOT NULL, random_id VARCHAR(255) NOT NULL, redirect_uris LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', secret VARCHAR(255) NOT NULL, allowed_grant_types LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', `label` VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pim_api_refresh_token (id INT AUTO_INCREMENT NOT NULL, client INT DEFAULT NULL, user INT DEFAULT NULL, token VARCHAR(255) NOT NULL, expires_at INT DEFAULT NULL, scope VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_264A45105F37A13B (token), INDEX IDX_264A4510C7440455 (client), INDEX IDX_264A45108D93D649 (user), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pim_api_access_token (id INT AUTO_INCREMENT NOT NULL, user INT DEFAULT NULL, token VARCHAR(255) NOT NULL, expires_at INT DEFAULT NULL, scope VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_BD5E40235F37A13B (token), INDEX IDX_BD5E40238D93D649 (user), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pim_api_auth_code (id INT AUTO_INCREMENT NOT NULL, token VARCHAR(255) NOT NULL, redirect_uri LONGTEXT NOT NULL, expires_at INT DEFAULT NULL, scope VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_AD5DC7C65F37A13B (token), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE pim_api_refresh_token ADD CONSTRAINT FK_264A4510C7440455 FOREIGN KEY (client) REFERENCES pim_api_client (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pim_api_refresh_token ADD CONSTRAINT FK_264A45108D93D649 FOREIGN KEY (user) REFERENCES oro_user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pim_api_access_token ADD CONSTRAINT FK_BD5E40238D93D649 FOREIGN KEY (user) REFERENCES oro_user (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
