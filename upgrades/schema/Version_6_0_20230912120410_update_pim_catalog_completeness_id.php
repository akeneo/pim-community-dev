<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_6_0_20230912120410_update_pim_catalog_completeness_id extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        if ($this->migrationWasAlreadyApplied()) {
            $this->removeMigrationWarning();
            return;
        }

        $this->addSql("ALTER TABLE pim_catalog_completeness MODIFY id bigint AUTO_INCREMENT");
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }

    private function removeMigrationWarning(): void
    {
        $this->addSql('SELECT 1');
    }

    private function migrationWasAlreadyApplied(): bool
    {
        $sql = <<<SQL
SELECT data_type
FROM information_schema.COLUMNS
WHERE table_schema = DATABASE()
AND table_name = 'pim_catalog_completeness'
AND column_name = 'id';
SQL;
        $result = $this->connection->executeQuery($sql)->fetchOne();

        return 'bigint' === $result;
    }
}
