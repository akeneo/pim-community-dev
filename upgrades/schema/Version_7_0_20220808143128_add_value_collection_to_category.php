<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Version_7_0_20220808143128_add_value_collection_to_category extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->skipIf(
            $schema->getTable('pim_catalog_category')->hasColumn('value_collection'),
            'value_collection column already exists in pim_catalog_category'
        );

        $this->addSql('ALTER TABLE pim_catalog_category ADD COLUMN value_collection JSON AFTER rgt, ALGORITHM=INPLACE, LOCK=NONE;');
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
