<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_7_0_20220920100616_add_product_mapping extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        if ($schema->getTable('akeneo_catalog')->hasColumn('product_mapping')) {
            $this->disableMigrationWarning();
            return;
        }

        $this->addSql(
            <<<SQL
            ALTER TABLE akeneo_catalog
            ADD product_mapping JSON NOT NULL DEFAULT (JSON_OBJECT()) AFTER product_value_filters
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
