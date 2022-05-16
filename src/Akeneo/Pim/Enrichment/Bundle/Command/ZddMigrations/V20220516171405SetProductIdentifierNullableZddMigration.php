<?php

namespace Akeneo\Pim\Enrichment\Bundle\Command\ZddMigrations;

use Doctrine\DBAL\Connection;

class V20220516171405SetProductIdentifierNullableZddMigration implements ZddMigration
{
    public function __construct(private Connection $connection)
    {
    }

    public function migrate(): void
    {
        if ($this->isColumnNullable('pim_catalog_product', 'identifier')) {
            return;
        }

        $this->connection->executeQuery(<<<SQL
            ALTER TABLE pim_catalog_product 
            MODIFY COLUMN identifier varchar(255) COLLATE utf8mb4_unicode_ci NULL,
            ALGORITHM=INPLACE,
            LOCK=NONE;
        SQL);
    }

    public function getName(): string
    {
        return 'SetProductIdentifierNullable';
    }

    private function isColumnNullable(string $tableName, string $columnName): bool
    {
        $schema = $this->connection->getDatabase();
        $sql = <<<SQL
            SELECT IS_NULLABLE 
            FROM information_schema.columns 
            WHERE table_schema=:schema 
              AND table_name=:tableName
              AND column_name=:columnName;
        SQL;

        $result = $this->connection->fetchOne($sql, [
            'schema' => $schema,
            'tableName' => $tableName,
            'columnName' => $columnName
        ]);

        return $result !== 'NO';
    }
}
