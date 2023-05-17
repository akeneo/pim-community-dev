<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Version_8_0_20230412073848_add_main_attribute_column extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds the is_main_identifier column';
    }

    public function up(Schema $schema): void
    {
        $this->skipIf(
            $schema->getTable('pim_catalog_attribute')->hasColumn('main_identifier'),
            'main_identifier column already exists in pim_catalog_attribute'
        );

        $this->addSql('ALTER TABLE pim_catalog_attribute ADD main_identifier TINYINT(1) NOT NULL DEFAULT FALSE;');
        $this->addSql(
            'UPDATE pim_catalog_attribute SET main_identifier = TRUE WHERE code = \'sku\';'
        );
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
