<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_8_0_20230116175236_add_product_identifiers_column extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add a new identifiers column to the pim_catalog_product table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            <<<SQL
            ALTER TABLE pim_catalog_product 
                ADD COLUMN `identifiers` JSON DEFAULT NULL COMMENT '(DC2Type:json_array)';
            SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
