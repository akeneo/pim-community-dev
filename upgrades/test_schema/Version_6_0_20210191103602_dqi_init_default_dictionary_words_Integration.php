<?php

declare(strict_types=1);

namespace Pim\Upgrade\test_schema;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Pim\Upgrade\Schema\Tests\ExecuteMigrationTrait;

final class Version_6_0_20210191103602_dqi_init_default_dictionary_words_Integration extends TestCase
{
    use ExecuteMigrationTrait;

    private const MIGRATION_LABEL = '_6_0_20210191103602_dqi_init_default_dictionary_words';

    public function test_it_adds_default_words_to_dictionary(): void
    {
        $this->activateLocale('fr_FR');
        $this->resetDictionary();
        $this->givenADictionaryWord('en_US', 'sku');
        $this->assertCountDictionaryWords(1);

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        $this->assertCountDictionaryWords(34);
        $this->assertDictionaryWordExists('en_US', 'sku');
        $this->assertDictionaryWordExists('en_US', 'xxl');
        $this->assertDictionaryWordExists('fr_FR', 'xxl');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function resetDictionary(): void
    {
        $this->get('database_connection')->executeQuery(<<<SQL
TRUNCATE TABLE pimee_data_quality_insights_text_checker_dictionary;
SQL
        );
    }

    private function givenADictionaryWord(string $locale, string $word): void
    {
        $query = <<<SQL
INSERT INTO pimee_data_quality_insights_text_checker_dictionary (locale_code, word) VALUES (:locale, :word);
SQL;

        $this->get('database_connection')->executeQuery($query, ['locale' => $locale, 'word' => $word]);
    }

    private function activateLocale(string $locale): void
    {
        $this->get('database_connection')->executeQuery(<<<SQL
UPDATE pim_catalog_locale SET is_activated = 1 WHERE code = :code;
SQL
        , ['code' => $locale]);
    }

    private function assertCountDictionaryWords(int $expectedCount): void
    {
        $count = $this->get('database_connection')->executeQuery(<<<SQL
SELECT COUNT(*) FROM pimee_data_quality_insights_text_checker_dictionary;
SQL
        )->fetchColumn();

        $this->assertSame($expectedCount, intval($count));
    }

    private function assertDictionaryWordExists(string $locale, string $word): void
    {
        $wordExists = $this->get('database_connection')->executeQuery(<<<SQL
SELECT 1 FROM pimee_data_quality_insights_text_checker_dictionary
WHERE locale_code = :locale AND word = :word;
SQL
        , ['locale' => $locale, 'word' => $word])->fetchColumn();

        $this->assertTrue(boolval($wordExists));
    }
}
