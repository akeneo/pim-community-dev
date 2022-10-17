<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_7_0_20221007115352_supplier_portal_add_contributor_account_consent_field extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->skipIf($this->alreadyModified($schema), 'The consent column already exists.');

        $sql = <<<SQL
            ALTER TABLE akeneo_supplier_portal_contributor_account ADD COLUMN consent TINYINT NOT NULL DEFAULT 0;
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
            AND TABLE_NAME = 'akeneo_supplier_portal_contributor_account'
            AND COLUMN_NAME = 'consent';
        SQL;

        $result = $this->connection->executeQuery($query, [
            'table_schema' => $schema->getName(),
        ])->fetchOne();

        return $result !== false;
    }
}
