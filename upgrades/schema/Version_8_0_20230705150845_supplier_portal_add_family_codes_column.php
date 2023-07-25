<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_8_0_20230705150845_supplier_portal_add_family_codes_column extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->skipIf(!$schema->hasTable('akeneo_supplier_portal_template_configuration'), 'The table does not exist.');
        $this->skipIf($this->alreadyModified($schema), 'The family_codes column already exists.');

        $sql = <<<SQL
            ALTER TABLE akeneo_supplier_portal_template_configuration ADD COLUMN family_codes JSON AFTER locale_code;
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
            AND COLUMN_NAME = 'family_codes';
        SQL;

        $result = $this->connection->executeQuery($query, [
            'table_schema' => $schema->getName(),
        ])->fetchOne();

        return $result !== false;
    }
}
