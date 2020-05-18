<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Migrations\IrreversibleMigrationException;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Drops the foreign keys of the PAM-related tables
 */
final class Version_4_0_20200506122439_drop_pam_foreign_keys extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() !== 'mysql',
            'Migration can only be executed safely on \'mysql\'.'
        );

        if ($this->tableExists('pimee_product_asset_channel_variation_configuration')) {
            $this->addSql('ALTER TABLE pimee_product_asset_channel_variation_configuration DROP FOREIGN KEY FK_FF39199B72F5A1AA;');
        }
        if ($this->tableExists('pimee_product_asset_variation')) {
            $this->addSql('ALTER TABLE pimee_product_asset_variation DROP FOREIGN KEY FK_F895CD872F5A1AA;');
        }
    }

    public function down(Schema $schema) : void
    {
        throw new IrreversibleMigrationException();
    }

    private function tableExists(string $tableName): bool
    {
        $rows = $this->connection->executeQuery(
            'SHOW TABLES LIKE :tableName',
            [
                'tableName' => $tableName,
            ]
        )->fetchAll();

        return count($rows) >= 1;
    }
}
