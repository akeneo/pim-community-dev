<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_7_0_20220830000000_add_catalog_product_values_filters extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        if ($schema->getTable('akeneo_catalog')->hasColumn('product_value_filters')) {
            $this->disableMigrationWarning();
            return;
        }

        $this->addSql(
            <<<SQL
            ALTER TABLE akeneo_catalog
            ADD product_value_filters JSON NOT NULL DEFAULT (JSON_OBJECT()) AFTER product_selection_criteria
            SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }

    private function disableMigrationWarning(): void
    {
        $this->addSql('SELECT 1');
    }
}
