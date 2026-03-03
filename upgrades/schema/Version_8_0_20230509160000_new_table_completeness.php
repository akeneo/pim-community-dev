<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Version_8_0_20230509160000_new_table_completeness extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create the new table for completeness with json values';
    }

    public function up(Schema $schema): void
    {
        $sql = <<<'SQL'
            CREATE TABLE IF NOT EXISTS pim_catalog_product_completeness(
                `product_uuid` binary(16) NOT NULL,
                `completeness` JSON NOT NULL DEFAULT (JSON_OBJECT()),
                PRIMARY KEY (`product_uuid`),
                CONSTRAINT `FK_PRODUCTUUID_COMPLETENESS` FOREIGN KEY (`product_uuid`) REFERENCES `pim_catalog_product` (`uuid`) ON DELETE CASCADE
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB
            SQL;

        $this->addSql($sql);
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
