<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Version_7_0_20220616074214_add_product_selection_criteria_field_to_catalogs extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->skipIf($schema->getTable('akeneo_catalog')->hasColumn('product_selection_criteria'), 'nothing to do');

        $this->addSql(
            <<<SQL
        ALTER TABLE akeneo_catalog
        ADD product_selection_criteria JSON NOT NULL DEFAULT (JSON_ARRAY()) AFTER is_enabled;
        SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
