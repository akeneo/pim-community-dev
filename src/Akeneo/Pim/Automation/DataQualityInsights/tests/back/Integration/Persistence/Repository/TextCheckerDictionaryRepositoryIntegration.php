<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Persistence\Repository;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\DictionaryWord;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository\TextCheckerDictionaryRepository;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

final class TextCheckerDictionaryRepositoryIntegration extends TestCase
{
    /** @var Connection */
    private $db;

    /** @var TextCheckerDictionaryRepository */
    private $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = $this->get('database_connection');
        $this->repository = $this->get(TextCheckerDictionaryRepository::class);
        $this->resetDictionary();
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_filters_words_that_are_the_dictionary()
    {
        $enUS = new LocaleCode('en_US');
        $sony = new DictionaryWord('sony');
        $samsung = new DictionaryWord('samsung');
        $panasonic = new DictionaryWord('panasonic');

        $this->repository->save(new Write\TextCheckerDictionaryWord($enUS, new DictionaryWord('sony')));
        $this->repository->save(new Write\TextCheckerDictionaryWord($enUS, new DictionaryWord('lg')));
        $this->repository->save(new Write\TextCheckerDictionaryWord($enUS, new DictionaryWord('samsung')));
        $this->repository->save(new Write\TextCheckerDictionaryWord(new LocaleCode('fr_FR'), new DictionaryWord('panasonic')));


        $filteredWords = $this->repository->filterExistingWords($enUS, [$sony, $samsung, $panasonic]);

        $this->assertEquals([$sony, $samsung], $filteredWords);
    }

    public function test_it_does_not_saves_the_same_word_several_times()
    {
        $this->repository->save(new Write\TextCheckerDictionaryWord(new LocaleCode('en_US'), new DictionaryWord('samsung')));
        $this->repository->save(new Write\TextCheckerDictionaryWord(new LocaleCode('en_US'), new DictionaryWord('Samsung')));
        $this->repository->save(new Write\TextCheckerDictionaryWord(new LocaleCode('en_US'), new DictionaryWord('samsung')));

        $this->assertCount(1, $this->repository->findByLocaleCode(new LocaleCode('en_US')));
    }

    public function test_it_saves_several_words_at_once_in_the_dictionary()
    {
        $this->createWords();
        $this->repository->saveAll([
            new Write\TextCheckerDictionaryWord(new LocaleCode('en_US'), new DictionaryWord('samsung')),
            new Write\TextCheckerDictionaryWord(new LocaleCode('en_US'), new DictionaryWord('Panasonic')),
            new Write\TextCheckerDictionaryWord(new LocaleCode('fr_FR'), new DictionaryWord('samsung')),
        ]);

        $this->assertDictionaryWordExists('en_US', 'samsung');
        $this->assertDictionaryWordExists('en_US', 'panasonic');
        $this->assertDictionaryWordExists('fr_FR', 'samsung');
    }

    public function test_it_saves_several_words_with_special_chars_at_once_in_the_dictionary()
    {
        $this->createWords();
        $this->repository->saveAll([
            new Write\TextCheckerDictionaryWord(new LocaleCode('fr_FR'), new DictionaryWord('ete')),
            new Write\TextCheckerDictionaryWord(new LocaleCode('fr_FR'), new DictionaryWord('été')),
            new Write\TextCheckerDictionaryWord(new LocaleCode('fr_FR'), new DictionaryWord('ètè')),
        ]);

        $this->assertDictionaryWordExists('fr_FR', 'ete');
        $this->assertDictionaryWordExists('fr_FR', 'été');
        $this->assertDictionaryWordExists('fr_FR', 'ètè');
    }

    public function test_it_returns_an_array_of_words_for_a_locale()
    {
        $this->createWords();

        $textCheckerDictionaryWords = $this->repository->findByLocaleCode(new LocaleCode('en_US'));
        $this->assertCount(3, $textCheckerDictionaryWords);
        $this->assertInstanceOf(Read\TextCheckerDictionaryWord::class, $textCheckerDictionaryWords[0]);
    }

    public function test_it_returns_an_empty_result_on_paginated_search()
    {
        $results = $this->repository->paginatedSearch(new LocaleCode('en_US'), 1, 25, '');
        $this->assertCount(0, $results['results']);
        $this->assertEquals(0, $results['total']);
    }

    public function test_it_returns_a_results_with_limit_offest()
    {
        $this->createWords();

        $results = $this->repository->paginatedSearch(new LocaleCode('en_US'), 1, 25, '');
        $this->assertCount(3, $results['results']);
        $this->assertEquals(3, $results['total']);

        $results = $this->repository->paginatedSearch(new LocaleCode('en_US'), 1, 1, '');
        $this->assertCount(1, $results['results']);
        $this->assertEquals(3, $results['total']);

        $results = $this->repository->paginatedSearch(new LocaleCode('en_US'), 2, 1, '');
        $this->assertCount(1, $results['results']);
        $this->assertEquals(3, $results['total']);
    }

    public function test_it_returns_results_on_existing_searched_word()
    {
        $this->createWords();

        $results = $this->repository->paginatedSearch(new LocaleCode('en_US'), 1, 25, 'Son');
        $this->assertCount(1, $results['results']);
        $this->assertEquals(1, $results['total']);
    }

    public function test_it_returns_nothing_on_non_existing_searched_word()
    {
        $this->createWords();

        $results = $this->repository->paginatedSearch(new LocaleCode('en_US'), 1, 25, 'Aken');
        $this->assertCount(0, $results['results']);
        $this->assertEquals(0, $results['total']);
    }

    public function test_it_retrieves_if_the_dictionary_is_empty_for_a_given_locale()
    {
        $this->repository->save(new Write\TextCheckerDictionaryWord(new LocaleCode('fr_FR'), new DictionaryWord('samsung')));
        $enUS = new LocaleCode('en_US');

        $this->assertTrue($this->repository->isEmptyForLocale($enUS));

        $this->repository->save(new Write\TextCheckerDictionaryWord($enUS, new DictionaryWord('samsung')));

        $this->assertFalse($this->repository->isEmptyForLocale($enUS));
    }

    public function test_it_deletes_a_word()
    {
        $this->createWords();

        $this->assertCount(3, $this->repository->findByLocaleCode(new LocaleCode('en_US')));

        $sql = <<<SQL
SELECT id FROM pimee_data_quality_insights_text_checker_dictionary
WHERE enabled = 1 AND locale_code = 'en_US'
LIMIT 1;
SQL;
        $wordId = $this->get('database_connection')->executeQuery($sql)->fetchOne();

        $this->repository->deleteWord((int) $wordId);
        $this->assertCount(2, $this->repository->findByLocaleCode(new LocaleCode('en_US')));
    }

    public function test_it_saves_without_changing_the_date_when_the_word_is_already_enabled(): void
    {
        $this->createWords();

        $alreadyEnabledWord = new Write\TextCheckerDictionaryWord(new LocaleCode('en_US'), new DictionaryWord('samsung'));

        $this->repository->saveAll([$alreadyEnabledWord]);
        $this->repository->save($alreadyEnabledWord);

        $query = <<<SQL
SELECT updated_at FROM pimee_data_quality_insights_text_checker_dictionary
WHERE locale_code = 'en_US' AND word = 'samsung';
SQL;

        $updatedAt = $this->get('database_connection')->executeQuery($query)->fetchOne();
        $this->assertEquals('2022-06-03 11:45:27', $updatedAt);
    }

    private function createWords()
    {
        $query = <<<SQL
 INSERT INTO pimee_data_quality_insights_text_checker_dictionary (locale_code, word, enabled, updated_at)
 VALUES
    ('en_US', 'samsung', 1, '2022-06-03 11:45:27'),
    ('en_US', 'Sony', 1, '2022-06-03 11:45:27'),
    ('en_US', 'LG', 1, '2022-06-03 11:45:27'),
    ('en_US', 'Asus', 0, '2022-06-03 11:45:27')
;
SQL;
        $this->get('database_connection')->executeQuery($query);
    }

    private function assertDictionaryWordExists(string $locale, string $word): void
    {
        $query = <<<SQL
SELECT 1 FROM pimee_data_quality_insights_text_checker_dictionary
WHERE locale_code = :locale AND BINARY word = :word AND enabled = 1;
SQL;

        $wordExists = $this->get('database_connection')->executeQuery($query, ['locale' => $locale, 'word' => $word])->fetchOne();

        $this->assertTrue(boolval($wordExists));
    }

    private function resetDictionary(): void
    {
        $this->get('database_connection')->executeQuery(
            <<<SQL
TRUNCATE TABLE pimee_data_quality_insights_text_checker_dictionary;
SQL
        );
    }
}
