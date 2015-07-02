<?php

namespace Pimee\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version_1_4_20150630141709_category_access extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $sql = "CREATE TABLE pimee_security_asset_category_access (id INT AUTO_INCREMENT NOT NULL, category_id INT NOT NULL, user_group_id SMALLINT NOT NULL, view_items TINYINT(1) DEFAULT '0' NOT NULL, edit_items TINYINT(1) DEFAULT '0' NOT NULL, own_items TINYINT(1) DEFAULT '0' NOT NULL, INDEX IDX_70DA129E12469DE2 (category_id), INDEX IDX_70DA129E1ED93D47 (user_group_id), UNIQUE INDEX category_user_group_idx (category_id, user_group_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
        ALTER TABLE pimee_security_asset_category_access ADD CONSTRAINT FK_70DA129E12469DE2 FOREIGN KEY (category_id) REFERENCES pimee_product_asset_category (id) ON DELETE CASCADE;
        ALTER TABLE pimee_security_asset_category_access ADD CONSTRAINT FK_70DA129E1ED93D47 FOREIGN KEY (user_group_id) REFERENCES oro_access_group (id) ON DELETE CASCADE;
        ALTER TABLE pimee_security_category_access
            CHANGE view_products view_items tinyint(1),
            CHANGE edit_products edit_items tinyint(1),
            CHANGE own_products own_items tinyint(1);
        RENAME TABLE pimee_security_category_access TO pimee_security_product_category_access;";

        $this->addSql($sql);
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        throw new \RuntimeException('No revert is provided for the migrations.');
    }
}
