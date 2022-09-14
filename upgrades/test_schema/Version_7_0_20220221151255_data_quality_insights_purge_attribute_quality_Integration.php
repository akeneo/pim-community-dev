<?php

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use Pim\Upgrade\Schema\Tests\ExecuteMigrationTrait;
use Webmozart\Assert\Assert;

class Version_7_0_20220221151255_data_quality_insights_purge_attribute_quality_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_7_0_20220221151255_data_quality_insights_purge_attribute_quality';

    public function test_it_adds_foreign_key_on_data_quality_insights_attribute_tables(): void
    {
        $this->removeForeignKey('pimee_dqi_attribute_locale_quality');
        $this->removeForeignKey('pimee_dqi_attribute_quality');

        $this->addAttributes();
        $this->addAttributesQuality();

        $this->removeAttribute();

        $this->assertAttributeHasQuality('pimee_dqi_attribute_locale_quality');
        $this->assertAttributeHasQuality('pimee_dqi_attribute_quality');

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $this->assertForeignKeyExists('pimee_dqi_attribute_locale_quality');
        $this->assertForeignKeyExists('pimee_dqi_attribute_quality');

        $this->assertAttributHasNoQuality('pimee_dqi_attribute_locale_quality');
        $this->assertAttributHasNoQuality('pimee_dqi_attribute_quality');
    }

    public function test_it_adds_foreign_key_on_data_quality_insights_attribute_tables_once(): void
    {
        $this->assertForeignKeyExists('pimee_dqi_attribute_locale_quality');
        $this->assertForeignKeyExists('pimee_dqi_attribute_quality');

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $this->assertForeignKeyExists('pimee_dqi_attribute_locale_quality');
        $this->assertForeignKeyExists('pimee_dqi_attribute_quality');
    }

    private function getForeignKey(string $tableName): ?string
    {
        $sql = <<<SQL
        SELECT CONSTRAINT_NAME
        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
        WHERE  CONSTRAINT_SCHEMA = :dbName
        AND TABLE_NAME = :tableName
        AND COLUMN_NAME = 'attribute_code'
        AND CONSTRAINT_NAME LIKE 'FK_%';
        SQL;

        $foreignKey = $this->getDbConnection()->executeQuery($sql, [
            'dbName' => $this->getDbConnection()->getDatabase(),
            'tableName' => $tableName
        ])->fetchOne();

        return is_string($foreignKey) ? $foreignKey : null;
    }

    private function removeForeignKey(string $tableName): void
    {
        $foreignKey = $this->getForeignKey($tableName);

        if (null === $foreignKey) {
            return;
        }

        $sql = <<<SQL
        ALTER TABLE $tableName
        DROP CONSTRAINT $foreignKey;
        SQL;

        $this->getDbConnection()->executeQuery($sql);
    }

    private function addAttributes(): void
    {
        $sql = <<<SQL
        INSERT INTO pim_catalog_attribute (sort_order, is_required, is_unique, is_localizable, is_scopable, code, entity_type, attribute_type, backend_type, created, updated)
        VALUES (0, 1, 1, 1, 1, 'name', 'entity' ,'type', 'type', NOW(), NOW()),
        (0, 1, 1, 1, 1, 'picture', 'entity','type', 'type', NOW(), NOW());
        SQL;

        $this->get('database_connection')->executeQuery($sql);
    }

    private function addAttributesQuality(): void
    {
        $sql = <<<SQL
        INSERT INTO pimee_dqi_attribute_locale_quality (attribute_code, locale, quality)
        VALUES ('name', 'fr_FR', '{}'),
        ('picture','fr_FR', '{}'),
        ('name', 'en_US', '{}'),
        ('picture','en_US', '{}');
        
        INSERT INTO pimee_dqi_attribute_quality (attribute_code, quality)
        VALUES ('name', '{}'),
        ('picture', '{}');
        SQL;

        $this->getDbConnection()->executeQuery($sql);
    }

    private function removeAttribute(): void
    {
        $sql = <<<SQL
        DELETE FROM pim_catalog_attribute
        WHERE code = 'name';
        SQL;

        $this->getDbConnection()->executeQuery($sql);
    }

    private function assertAttributeHasQuality(string $tableName): void
    {
        $sql = <<<SQL
        SELECT 1 FROM $tableName
        WHERE attribute_code = 'name';
        SQL;

        $result = $this->getDbConnection()->executeQuery($sql)->fetchOne();
        Assert::same($result,'1');
    }

    private function assertForeignKeyExists(string $tableName): void
    {
        $foreignKey = $this->getForeignKey($tableName);
        Assert::notNull($foreignKey, 'Foreign key does not exist on table ' . $tableName);
    }

    private function assertAttributHasNoQuality(string $tableName): void
    {
        $sql = <<<SQL
        SELECT COUNT(1) FROM $tableName
        WHERE attribute_code = 'name';
        SQL;

        $result = $this->getDbConnection()->executeQuery($sql)->fetchOne();
        Assert::same($result,'0');
    }

    private function getDbConnection(): Connection
    {
        return $this->get('database_connection');
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
