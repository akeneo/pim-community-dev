<?php

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version_2_1_171129131942_product_model_associations extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        if (!$this->tableAlreadyExists()) {
            $this->createTable();
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE pim_catalog_association_product_model');
    }

    /**
     * @return bool
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    private function tableAlreadyExists(): bool
    {
        $stmt = $this->connection->executeQuery('SHOW TABLES LIKE \'pim_catalog_association_product_model\';');

        return 1 <= $stmt->rowCount();
    }

    /**
     * Creates a new table and adds constraints
     */
    private function createTable(): void
    {
        $this->addSql('CREATE TABLE pim_catalog_association_product_model (association_id INT NOT NULL, product_model_id INT NOT NULL, INDEX IDX_378B82C7EFB9C8A5 (association_id), INDEX IDX_378B82C7B2C5DD70 (product_model_id), PRIMARY KEY(association_id, product_model_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE pim_catalog_association_product_model ADD CONSTRAINT FK_378B82C7EFB9C8A5 FOREIGN KEY (association_id) REFERENCES pim_catalog_association (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pim_catalog_association_product_model ADD CONSTRAINT FK_378B82C7B2C5DD70 FOREIGN KEY (product_model_id) REFERENCES pim_catalog_product_model (id) ON DELETE CASCADE');
    }
}
