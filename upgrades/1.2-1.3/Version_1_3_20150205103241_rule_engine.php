<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Class Version_1_3_20150205103241_rule_engine
 *
 * @author    Stephane Chapeau <stephane.chapeau@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Version_1_3_20150205103241_rule_engine extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE akeneo_rule_engine_rule_definition (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(100) NOT NULL, type VARCHAR(100) NOT NULL, content LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json_array)\', priority INT DEFAULT 0 NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pimee_catalog_rule_rule_relation (id INT AUTO_INCREMENT NOT NULL, rule_id INT DEFAULT NULL, resource_name VARCHAR(255) NOT NULL, resource_id VARCHAR(24) NOT NULL, INDEX IDX_6FCDB8EE744E0351 (rule_id), INDEX resource_name_resource_id_idx (resource_name, resource_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE pimee_catalog_rule_rule_relation ADD CONSTRAINT FK_6FCDB8EE744E0351 FOREIGN KEY (rule_id) REFERENCES akeneo_rule_engine_rule_definition (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema)
    {
        throw new \RuntimeException('No revert is provided for the migrations.');
    }
}
