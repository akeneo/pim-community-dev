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
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_exists()
    {
        $this->createWords();
        $this->assertTrue($this->repository->exists(new LocaleCode('en_US'), new DictionaryWord('samsung')));
        $this->assertFalse($this->repository->exists(new LocaleCode('en_US'), new DictionaryWord('Samsung')));
        $this->assertFalse($this->repository->exists(new LocaleCode('en_GB'), new DictionaryWord('samsung')));
        $this->assertFalse($this->repository->exists(new LocaleCode('fr_FR'), new DictionaryWord('Sony')));
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

    private function createWords()
    {
        $query = <<<SQL
 INSERT INTO pimee_data_quality_insights_text_checker_dictionary (locale_code, word)
 VALUES
    ('en_US', 'samsung'),
    ('en_US', 'Sony'),
    ('en_US', 'LG')
;
SQL;
        $this->get('database_connection')->executeQuery($query);
    }

    private function assertDictionaryWordExists(string $locale, string $word): void
    {
        $wordExists = $this->get('database_connection')->executeQuery(<<<SQL
SELECT 1 FROM pimee_data_quality_insights_text_checker_dictionary 
WHERE locale_code = :locale AND word = :word;
SQL
            ,['locale' => $locale, 'word' => $word]
        )->fetchColumn();

        $this->assertTrue(boolval($wordExists));
    }
}
