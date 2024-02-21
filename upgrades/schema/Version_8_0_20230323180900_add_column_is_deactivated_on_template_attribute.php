<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Version_8_0_20230323180900_add_column_is_deactivated_on_template_attribute extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->skipIf(
            $schema->getTable('pim_catalog_category_attribute')->hasColumn('is_deactivated'),
            'is_deactivated column already exists in pim_catalog_category_attribute'
        );

        $this->addSql('ALTER TABLE pim_catalog_category_attribute ADD COLUMN is_deactivated BOOLEAN NOT NULL DEFAULT 0;');
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
