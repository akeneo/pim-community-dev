<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Class Version20160312201959
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Version_1_6_20160312201959_batch_mapping extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE akeneo_batch_mapping_field DROP FOREIGN KEY FK_41EB4888126F525E');
        $this->addSql('DROP TABLE akeneo_batch_mapping_field');
        $this->addSql('DROP TABLE akeneo_batch_mapping_item');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE akeneo_batch_mapping_field (id INT AUTO_INCREMENT NOT NULL, item_id INT DEFAULT NULL, source VARCHAR(255) NOT NULL, destination VARCHAR(255) NOT NULL, identifier TINYINT(1) NOT NULL, INDEX IDX_41EB4888126F525E (item_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE akeneo_batch_mapping_item (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE akeneo_batch_mapping_field ADD CONSTRAINT FK_41EB4888126F525E FOREIGN KEY (item_id) REFERENCES akeneo_batch_mapping_item (id)');
    }
}
