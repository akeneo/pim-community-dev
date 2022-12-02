<?php

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

final class Version_7_0_20221129093347_add_cascade_on_prefixes_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private Connection $connection;
    private const MIGRATION_LABEL = '_7_0_20221129093347_add_cascade_on_prefixes';

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
    }

    public function test_it_adds_cascade(): void
    {
        $this->rollback();
        Assert::assertFalse($this->hasCascadeDelete('FK_PRODUCTUUID'));
        Assert::assertFalse($this->hasCascadeDelete('FK_ATTRIBUTEID'));
        $this->reExecuteMigration(self::MIGRATION_LABEL);
        Assert::assertTrue($this->hasCascadeDelete('FK_PRODUCTUUID'));
        Assert::assertTrue($this->hasCascadeDelete('FK_ATTRIBUTEID'));
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function rollback()
    {
        $sql = <<<SQL
ALTER TABLE pim_catalog_identifier_generator_prefixes 
    DROP FOREIGN KEY `FK_PRODUCTUUID`;
ALTER TABLE pim_catalog_identifier_generator_prefixes 
    ADD CONSTRAINT `FK_PRODUCTUUID`
        FOREIGN KEY (`product_uuid`)
        REFERENCES `pim_catalog_product` (`uuid`);
ALTER TABLE pim_catalog_identifier_generator_prefixes 
    DROP FOREIGN KEY `FK_ATTRIBUTEID`;
ALTER TABLE pim_catalog_identifier_generator_prefixes 
    ADD CONSTRAINT `FK_ATTRIBUTEID`
        FOREIGN KEY (`attribute_id`)
        REFERENCES `pim_catalog_attribute` (`id`) ;
SQL;
        $this->connection->executeQuery($sql);
    }

    private function hasCascadeDelete(string $fkName): bool
    {
        $sql = <<<SQL
SELECT DELETE_RULE
FROM information_schema.REFERENTIAL_CONSTRAINTS 
WHERE CONSTRAINT_NAME="%s"
AND CONSTRAINT_SCHEMA="%s"
SQL;

        $deleteRule = $this->connection->fetchOne(\sprintf($sql, $fkName, $this->connection->getDatabase()));

        return $deleteRule === 'CASCADE';
    }
}
