<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Version_6_0_20220524145600_add_updated_index_on_category extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->skipIf($this->indexExists(), 'Indexed updated_idx already exists in pim_catalog_category');

        $this->addSql('CREATE INDEX updated_idx ON pim_catalog_category (updated)');
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }

    private function indexExists(): bool
    {
        $indexes = $this->connection->executeQuery('SHOW INDEX FROM pim_catalog_category')->fetchAllAssociative();
        $indexesIndexedByName = array_column($indexes, null, 'Key_name');

        return isset(
            $indexesIndexedByName['updated_idx'],
        );
    }
}
