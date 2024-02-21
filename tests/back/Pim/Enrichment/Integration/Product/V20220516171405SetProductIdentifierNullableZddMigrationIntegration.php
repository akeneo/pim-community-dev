<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Product;

use Akeneo\Pim\Enrichment\Bundle\Command\ZddMigrations\V20220516171405SetProductIdentifierNullableZddMigration;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

class V20220516171405SetProductIdentifierNullableZddMigrationIntegration extends TestCase
{
    public function test_it_set_the_identifier_column_nullable()
    {
        $this->setNotNullableColumn();
        Assert::assertFalse($this->isColumnNullable('pim_catalog_product', 'identifier'));
        $this->runMigration();
        Assert::assertTrue($this->isColumnNullable('pim_catalog_product', 'identifier'));
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function runMigration(): void
    {
        $this->getMigration()->migrate();
    }

    private function isColumnNullable(string $tableName, string $columnName): bool
    {
        $schema = $this->getConnection()->getDatabase();
        $sql = <<<SQL
            SELECT IS_NULLABLE 
            FROM information_schema.columns 
            WHERE table_schema=:schema 
              AND table_name=:tableName
              AND column_name=:columnName;
        SQL;

        $result = $this->getConnection()->fetchOne($sql, [
            'schema' => $schema,
            'tableName' => $tableName,
            'columnName' => $columnName
        ]);

        return $result === 'YES';
    }

    private function setNotNullableColumn(): void
    {
        $this->getConnection()->executeQuery(<<<SQL
            ALTER TABLE pim_catalog_product 
            MODIFY COLUMN identifier varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
        SQL);
    }

    private function getConnection(): Connection
    {
        return $this->get('database_connection');
    }

    private function getMigration(): V20220516171405SetProductIdentifierNullableZddMigration
    {
        return $this->get(V20220516171405SetProductIdentifierNullableZddMigration::class);
    }
}
