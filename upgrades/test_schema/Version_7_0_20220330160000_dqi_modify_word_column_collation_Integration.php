<?php

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use Pim\Upgrade\Schema\Tests\ExecuteMigrationTrait;

class Version_7_0_20220330160000_dqi_modify_word_column_collation_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_7_0_20220330160000_dqi_modify_word_column_collation';

    public function test_it_modify_word_column(): void
    {
        $this->resetColumn();

        $this->insertWord('été', 'fr_FR');

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $schemaName = $this->getConnection()->getDatabase();
        $this->assertTrue($this->isModified($schemaName));
        $this->assertTrue($this->wordExists('été', 'fr_FR'));
    }

    public function test_migration_is_idempotent(): void
    {
        $this->resetColumn();

        $this->insertWord('ètè', 'fr_FR');

        $this->reExecuteMigration(self::MIGRATION_LABEL);
        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $schemaName = $this->getConnection()->getDatabase();
        $this->assertTrue($this->isModified($schemaName));
        $this->assertTrue($this->wordExists('ètè', 'fr_FR'));
    }

    private function resetColumn(): void
    {
        $query = <<<SQL
ALTER TABLE pimee_data_quality_insights_text_checker_dictionary 
MODIFY COLUMN word VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;
SQL;

        $this->getConnection()->executeQuery($query);
    }

    private function insertWord(string $word, string $locale): void
    {
        $query = <<<SQL
INSERT INTO pimee_data_quality_insights_text_checker_dictionary (word, locale_code)
VALUES (:word, :locale_code);
SQL;

        $this->getConnection()->executeQuery($query, ['word' => $word, 'locale_code' => $locale]);
    }

    private function wordExists(string $word, string $locale): bool
    {
        $query = <<<SQL
SELECT 1 FROM pimee_data_quality_insights_text_checker_dictionary
WHERE locale_code = :locale_code AND BINARY word = :word;
SQL;

        $wordExists = $this->get('database_connection')->executeQuery($query, ['locale_code' => $locale, 'word' => $word])->fetchOne();

        return boolval($wordExists);
    }

    private function isModified(string $tableSchema): bool
    {
        $query = <<<SQL
SELECT CHARACTER_SET_NAME, COLLATION_NAME FROM information_schema.`COLUMNS`
WHERE TABLE_SCHEMA = :table_schema
AND TABLE_NAME = 'pimee_data_quality_insights_text_checker_dictionary'
AND COLUMN_NAME = 'word';
SQL;

        $result = $this->getConnection()->executeQuery($query, [
            'table_schema' => $tableSchema,
        ])->fetchAssociative();

        return 'utf8mb4' === $result['CHARACTER_SET_NAME'] && 'utf8mb4_bin' === $result['COLLATION_NAME'];
    }

    private function getConnection(): Connection
    {
        return $this->get('database_connection');
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
