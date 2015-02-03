<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150203164225 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE pim_notification_user_notification (id INT AUTO_INCREMENT NOT NULL, notification INT DEFAULT NULL, user INT DEFAULT NULL, viewed TINYINT(1) NOT NULL, INDEX IDX_342AA855BF5476CA (notification), INDEX IDX_342AA8558D93D649 (user), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pim_notification_notification (id INT AUTO_INCREMENT NOT NULL, route VARCHAR(255) DEFAULT NULL, routeParams LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', message VARCHAR(255) NOT NULL, messageParams LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', created DATETIME NOT NULL, type VARCHAR(20) NOT NULL, context LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pim_catalog_product_template (id INT AUTO_INCREMENT NOT NULL, valuesData LONGTEXT NOT NULL COMMENT \'(DC2Type:json_array)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pim_comment_comment (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, author_id INT DEFAULT NULL, resource_name VARCHAR(255) NOT NULL, resource_id VARCHAR(24) NOT NULL, body LONGTEXT NOT NULL, created_at DATETIME NOT NULL, replied_at DATETIME NOT NULL, INDEX IDX_2A32D03D727ACA70 (parent_id), INDEX IDX_2A32D03DF675F31B (author_id), INDEX resource_name_resource_id_idx (resource_name, resource_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE pim_notification_user_notification ADD CONSTRAINT FK_342AA855BF5476CA FOREIGN KEY (notification) REFERENCES pim_notification_notification (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pim_notification_user_notification ADD CONSTRAINT FK_342AA8558D93D649 FOREIGN KEY (user) REFERENCES oro_user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pim_comment_comment ADD CONSTRAINT FK_2A32D03D727ACA70 FOREIGN KEY (parent_id) REFERENCES pim_comment_comment (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pim_comment_comment ADD CONSTRAINT FK_2A32D03DF675F31B FOREIGN KEY (author_id) REFERENCES oro_user (id)');
        $this->addSql('ALTER TABLE akeneo_batch_job_execution ADD `user` VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE pim_catalog_group ADD product_template_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE pim_catalog_group ADD CONSTRAINT FK_5D6997EDA9F591A7 FOREIGN KEY (product_template_id) REFERENCES pim_catalog_product_template (id) ON DELETE SET NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5D6997EDA9F591A7 ON pim_catalog_group (product_template_id)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE pim_notification_user_notification DROP FOREIGN KEY FK_342AA855BF5476CA');
        $this->addSql('ALTER TABLE pim_catalog_group DROP FOREIGN KEY FK_5D6997EDA9F591A7');
        $this->addSql('ALTER TABLE pim_comment_comment DROP FOREIGN KEY FK_2A32D03D727ACA70');
        $this->addSql('DROP TABLE pim_notification_user_notification');
        $this->addSql('DROP TABLE pim_notification_notification');
        $this->addSql('DROP TABLE pim_catalog_product_template');
        $this->addSql('DROP TABLE pim_comment_comment');
        $this->addSql('ALTER TABLE akeneo_batch_job_execution DROP `user`');
        $this->addSql('DROP INDEX UNIQ_5D6997EDA9F591A7 ON pim_catalog_group');
        $this->addSql('ALTER TABLE pim_catalog_group DROP product_template_id');
    }
}
