<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Version_8_0_20230308145407_add_sort_order_for_identifier_generator extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds a sort_order column for pim_catalog_identifier_generator table';
    }

    public function up(Schema $schema): void
    {
        $this->skipIf(
            $schema->getTable('pim_catalog_identifier_generator')->hasColumn('sort_order'),
            'Column sort_order already exist for pim_catalog_identifier_generator table'
        );

        $this->addSql('ALTER TABLE pim_catalog_identifier_generator ADD COLUMN sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 0;');

    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
