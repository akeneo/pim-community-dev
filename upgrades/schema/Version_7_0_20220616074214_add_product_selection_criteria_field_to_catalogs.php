<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version_7_0_20220616074214_add_product_selection_criteria_field_to_catalogs extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        if ($schema->getTable('akeneo_catalog')->hasColumn('product_selection_criteria')) {
            $this->disableMigrationWarning();
        }

        $this->addSql(<<<SQL
        ALTER TABLE akeneo_catalog
        ADD product_selection_criteria JSON NOT NULL DEFAULT (JSON_ARRAY());
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
