<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Version_8_0_20230523094142_create_product_identifiers_table extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create the pim_catalog_product_identifiers table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            <<<SQL
            CREATE TABLE IF NOT EXISTS pim_catalog_product_identifiers(
                product_uuid BINARY(16) NOT NULL PRIMARY KEY,
                identifiers JSON NOT NULL DEFAULT (JSON_ARRAY()),
                CONSTRAINT pim_catalog_product_identifiers_pim_catalog_product_uuid_fk
                    FOREIGN KEY (product_uuid) REFERENCES `pim_catalog_product` (uuid)
                        ON DELETE CASCADE,
                INDEX idx_identifiers ( (CAST(identifiers AS CHAR(511) ARRAY)) )
            )
            SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
