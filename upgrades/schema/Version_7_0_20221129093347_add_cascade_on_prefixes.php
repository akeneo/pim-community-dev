<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Table was generated without DELETE CASCALE. This migration adds it.
 * This table is empty in production, so it will be instant.
 */
final class Version_7_0_20221129093347_add_cascade_on_prefixes extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds DELETE CASCADE on pim_catalog_identifier_generator_prefixes foreign keys';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('SELECT 1');

        if (!$this->hasCascadeDelete('FK_PRODUCTUUID')) {
            $sql = <<<SQL
ALTER TABLE pim_catalog_identifier_generator_prefixes 
    DROP FOREIGN KEY `FK_PRODUCTUUID`;
ALTER TABLE pim_catalog_identifier_generator_prefixes 
    ADD CONSTRAINT `FK_PRODUCTUUID`
        FOREIGN KEY (`product_uuid`)
        REFERENCES `pim_catalog_product` (`uuid`) 
        ON DELETE CASCADE;
SQL;

            $this->addSql($sql);
        }

        if (!$this->hasCascadeDelete('FK_ATTRIBUTEID')) {
            $sql = <<<SQL
ALTER TABLE pim_catalog_identifier_generator_prefixes 
    DROP FOREIGN KEY `FK_ATTRIBUTEID`;
ALTER TABLE pim_catalog_identifier_generator_prefixes 
    ADD CONSTRAINT `FK_ATTRIBUTEID`
        FOREIGN KEY (`attribute_id`)
        REFERENCES `pim_catalog_attribute` (`id`) 
        ON DELETE CASCADE;
SQL;
            $this->addSql($sql);
        }
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }


    private function hasCascadeDelete(string $fkName): bool
    {
        $sql = <<<SQL
SELECT DELETE_RULE
FROM information_schema.REFERENTIAL_CONSTRAINTS 
WHERE CONSTRAINT_NAME="%s"
AND CONSTRAINT_SCHEMA="%s"
SQL;

        $deleteRule = $this->connection->fetchOne(\sprintf($sql, $fkName, $this->connection->getDatabase()));

        return $deleteRule === 'CASCADE';
    }
}
