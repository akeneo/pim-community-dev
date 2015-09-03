<?php

namespace Pimee\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150901171345 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()
                ->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE oro_user ADD defaultAssetTree_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE oro_user ADD CONSTRAINT FK_F82840BC4B574F10 FOREIGN KEY (defaultAssetTree_id) REFERENCES pimee_product_asset_category (id)');
        $this->addSql('CREATE INDEX IDX_F82840BC4B574F10 ON oro_user (defaultAssetTree_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()
                ->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE oro_user DROP FOREIGN KEY FK_F82840BC4B574F10');
        $this->addSql('DROP INDEX IDX_F82840BC4B574F10 ON oro_user');
        $this->addSql('ALTER TABLE oro_user DROP defaultAssetTree_id');
    }
}
