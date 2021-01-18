<?php

declare(strict_types=1);

namespace Pim\Upgrade\test_schema;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Pim\Upgrade\Schema\Tests\ExecuteMigrationTrait;

final class Version_6_0_20210118091114_dqi_add_uniqueness_constraint_on_dictionary_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_6_0_20210118091114_dqi_add_uniqueness_constraint_on_dictionary';

    public function test_it_add_uniqueness_constraint_on_dictionary()
    {
        $resetDictionaryTable = <<<SQL
DROP TABLE pimee_data_quality_insights_text_checker_dictionary;

CREATE TABLE pimee_data_quality_insights_text_checker_dictionary (
    id INT AUTO_INCREMENT NOT NULL,
    locale_code VARCHAR(20) NOT NULL,
    word VARCHAR(250) NOT NULL,
    PRIMARY KEY (id),
    INDEX word_index (word)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO pimee_data_quality_insights_text_checker_dictionary (locale_code, word) VALUES
('en_US', 'sku'),
('en_US', 'SKU'),
('en_US', 'erp'),
('fr_FR', 'sku'),
('fr_FR', 'erp');
SQL;

        $this->get('database_connection')->executeQuery($resetDictionaryTable);
        $this->assertDictionaryCount(5);

        $this->reExecuteMigration(self::MIGRATION_LABEL);
        $this->assertDictionaryCount(4);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function assertDictionaryCount(int $expectedCount): void
    {
        $countDictionaryWords = <<<SQL
SELECT COUNT(*) FROM pimee_data_quality_insights_text_checker_dictionary;
SQL;

        $count = intval($this->get('database_connection')->executeQuery($countDictionaryWords)->fetchColumn());

        $this->assertSame($expectedCount, $count);
    }
}
