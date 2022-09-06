<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_7_0_20220829142500_rename_akeneo_supplier_portal_supplier_file_table extends AbstractMigration
{
    private const TABLE_NAME = 'akeneo_supplier_portal_supplier_file';

    public function up(Schema $schema): void
    {
        if (!$this->tableExists(self::TABLE_NAME)) {
            return;
        }

        $sql = <<<SQL
            RENAME TABLE akeneo_supplier_portal_supplier_file TO akeneo_supplier_portal_supplier_product_file;
        SQL;

        $this->addSql($sql);
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }

    private function tableExists(string $tableName): bool
    {
        return $this->connection->executeQuery(
                'SHOW TABLES LIKE :tableName', ['tableName' => $tableName]
            )->rowCount() >= 1;
    }
}
