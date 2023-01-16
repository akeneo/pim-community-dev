<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_8_0_20230116175236_update_product_identifier_column extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Change the product identifier column to use a virtual generated column';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<SQL
            ALTER TABLE pim_catalog_product 
                ADD identifier2 VARCHAR(255) GENERATED ALWAYS AS (raw_values->>'$.sku."<all_channels>"."<all_locales>"') VIRTUAL;
            ALTER TABLE pim_catalog_product DROP INDEX UNIQ_91CD19C0772E836A, DROP COLUMN identifier;
            ALTER TABLE pim_catalog_product RENAME COLUMN identifier2 TO identifier;
            ALTER TABLE pim_catalog_product ADD UNIQUE INDEX UNIQ_91CD19C0772E836A (identifier);
            SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
