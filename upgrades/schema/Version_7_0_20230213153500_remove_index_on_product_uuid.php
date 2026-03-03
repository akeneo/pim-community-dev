<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_7_0_20230213153500_remove_index_on_product_uuid extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->disableMigrationWarning();
        if ($this->uuidIndexExist()) {
            $this->removeUuidIndex();
        }
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }

    private function uuidIndexExist(): bool
    {
        $query = <<<SQL
            SELECT COUNT(1) indexCount
            FROM INFORMATION_SCHEMA.STATISTICS
            WHERE table_schema = DATABASE()
            AND table_name = 'pim_catalog_product'
            AND index_name = 'product_uuid';
SQL;

        $result = $this->connection
            ->executeQuery($query)
            ->fetchOne();

        return (int) $result !== 0;
    }

    private function disableMigrationWarning(): void
    {
        $this->addSql('SELECT 1');
    }

    private function removeUuidIndex()
    {
        $query = <<<SQL
DROP INDEX product_uuid ON pim_catalog_product
SQL;

        $this->addSql($query);
    }
}
