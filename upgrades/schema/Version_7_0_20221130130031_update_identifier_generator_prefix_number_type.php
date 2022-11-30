<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * This migration updates `number` column type of pim_catalog_identifier_generator_prefixes from INT to BIGINT UNSIGNED.
 * This table is empty in production, so it will be instant.
 */
final class Version_7_0_20221130130031_update_identifier_generator_prefix_number_type extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Updates `number` column type of pim_catalog_identifier_generator_prefixes from INT to BIGINT UNSIGNED';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<SQL
ALTER TABLE pim_catalog_identifier_generator_prefixes 
    MODIFY `number` BIGINT UNSIGNED NOT NULL;
SQL);
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
