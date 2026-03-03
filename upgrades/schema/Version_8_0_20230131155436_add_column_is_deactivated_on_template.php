<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Version_8_0_20230131155436_add_column_is_deactivated_on_template extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->skipIf(
            $schema->getTable('pim_catalog_category_template')->hasColumn('is_deactivated'),
            'is_deactivated column already exists in pim_catalog_category_template'
        );

        $this->addSql('ALTER TABLE pim_catalog_category_template ADD COLUMN is_deactivated BOOLEAN;');
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
