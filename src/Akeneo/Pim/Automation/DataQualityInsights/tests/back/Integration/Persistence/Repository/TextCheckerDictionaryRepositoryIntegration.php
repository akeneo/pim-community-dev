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

    public function test_it_does_not_saves_words_with_same_case()
    {
        $this->repository->save(new Write\TextCheckerDictionaryWord(new LocaleCode('en_US'), new DictionaryWord('samsung')));
        $this->repository->save(new Write\TextCheckerDictionaryWord(new LocaleCode('en_US'), new DictionaryWord('Samsung')));
        $this->repository->save(new Write\TextCheckerDictionaryWord(new LocaleCode('en_US'), new DictionaryWord('samsung')));

        $this->assertCount(2, $this->repository->findByLocaleCode(new LocaleCode('en_US')));
    }

    public function test_it_returns_an_array_of_words_for_a_locale()
    {
        $this->repository->save(new Write\TextCheckerDictionaryWord(new LocaleCode('en_US'), new DictionaryWord('samsung')));
        $this->repository->save(new Write\TextCheckerDictionaryWord(new LocaleCode('en_US'), new DictionaryWord('Samsung')));
        $this->repository->save(new Write\TextCheckerDictionaryWord(new LocaleCode('en_US'), new DictionaryWord('SamSung')));

        $textCheckerDictionaryWords = $this->repository->findByLocaleCode(new LocaleCode('en_US'));
        $this->assertCount(3, $textCheckerDictionaryWords);
        $this->assertInstanceOf(Read\TextCheckerDictionaryWord::class, $textCheckerDictionaryWords[0]);
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
}
