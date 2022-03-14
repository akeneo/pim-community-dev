<?php

namespace Pim\Upgrade\test_schema;

use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use Pim\Upgrade\Schema\Tests\ExecuteMigrationTrait;
use Webmozart\Assert\Assert;

class Version_7_0_20220314172032_dqi_add_foreign_keys_on_pimee_dqi_attribute_option_spellcheck_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_7_0_20220314172032_dqi_add_foreign_keys_on_pimee_dqi_attribute_option_spellcheck';

    public function test_it_adds_foreign_keys_on_pimee_dqi_attribute_option_spellcheck(): void
    {
        $this->removeForeignKeys();

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $this->assertForeignKeysExist();
    }

    private function removeForeignKeys(): void
    {
        $foreignKeys = $this->getForeignKeys();

        if (null === $foreignKeys) {
            return;
        }

        if (!is_array($foreignKeys)) {
            return;
        }

        if (array_key_exists('CONSTRAINT_NAME', $foreignKeys)) {
            return;
        }

        foreach ($foreignKeys as $foreignKey) {
            $constraintName = $foreignKey['CONSTRAINT_NAME'];

            $sql = <<<SQL
            ALTER TABLE pimee_dqi_attribute_option_spellcheck
            DROP CONSTRAINT $constraintName;
            SQL;

            $this->getDbConnection()->executeQuery($sql);
        }
    }

    private function getForeignKeys(): ?array
    {
        $sql = <<<SQL
        SELECT CONSTRAINT_NAME
        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
        WHERE CONSTRAINT_SCHEMA = :dbName
        AND TABLE_NAME = 'pimee_dqi_attribute_option_spellcheck'
        AND CONSTRAINT_NAME LIKE 'FK_%';
        SQL;

        $foreignKeys = $this->getDbConnection()->executeQuery($sql, [
            'dbName' => $this->getDbConnection()->getDatabase()
        ])->fetchAllAssociative();

        return $foreignKeys;
    }

    private function assertForeignKeysExist(): void
    {
        $foreignKeys = $this->getForeignKeys();

        $this->assertEquals([
            0 => [
                'CONSTRAINT_NAME' => 'FK_dqi_attribute_code'
            ],
            1 => [
                'CONSTRAINT_NAME' => 'FK_dqi_attribute_option_code'
            ]
        ], $foreignKeys);
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
