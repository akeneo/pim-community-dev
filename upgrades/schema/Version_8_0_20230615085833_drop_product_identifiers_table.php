<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Version_8_0_20230615085833_drop_product_identifiers_table extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Drops the product identifiers table';
    }

    public function up(Schema $schema): void
    {
        $this->connection->executeStatement(<<<SQL
            DROP TABLE IF EXISTS `pim_catalog_product_identifiers`
        SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
