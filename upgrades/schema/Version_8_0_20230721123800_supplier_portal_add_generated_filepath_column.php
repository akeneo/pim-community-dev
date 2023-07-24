<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_8_0_20230721123800_supplier_portal_add_generated_filepath_column extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->skipIf($this->alreadyModified($schema), 'The generated_filepath column already exists.');

        $sql = <<<SQL
            ALTER TABLE akeneo_supplier_portal_template_configuration ADD COLUMN generated_filepath TEXT NULL;
        SQL;

        $this->addSql($sql);
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }

    private function alreadyModified(Schema $schema): bool
    {
        $query = <<<SQL
            SELECT 1 FROM information_schema.`COLUMNS`
            WHERE TABLE_SCHEMA = :table_schema
            AND TABLE_NAME = 'akeneo_supplier_portal_template_configuration'
            AND COLUMN_NAME = 'generated_filepath';
        SQL;

        $result = $this->connection->executeQuery($query, [
            'table_schema' => $schema->getName(),
        ])->fetchOne();

        return $result !== false;
    }
}
