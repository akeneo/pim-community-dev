<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Persistence\Query\Dictionary;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write\TextCheckerDictionaryWord;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\DictionaryWord;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Dictionary\GetNumberOfWordsQuery;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository\TextCheckerDictionaryRepository;
use Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\DataQualityInsightsTestCase;

final class GetNumberOfWordsQueryIntegration extends DataQualityInsightsTestCase
{
    public function test_it_retrieves_the_number_of_words_by_locales()
    {
        $this->givenAnEmptyDictionary();

        $noWords = $this->get(GetNumberOfWordsQuery::class)->byLocales(['en_US', 'fr_FR']);
        $this->assertSame([], $noWords);

        $enUS = new LocaleCode('en_US');
        $frFR = new LocaleCode('fr_FR');
        $dictionaryWordRepository = $this->get(TextCheckerDictionaryRepository::class);
        $dictionaryWordRepository->save(new TextCheckerDictionaryWord($enUS, new DictionaryWord('Ziggy')));
        $dictionaryWordRepository->save(new TextCheckerDictionaryWord($enUS, new DictionaryWord('Akeneo')));
        $dictionaryWordRepository->save(new TextCheckerDictionaryWord($frFR, new DictionaryWord('Akeneo')));

        $numberOfWords = $this->get(GetNumberOfWordsQuery::class)->byLocales(['en_US', 'fr_FR']);

        $this->assertEqualsCanonicalizing(['en_US' => 2, 'fr_FR' => 1], $numberOfWords);
    }

    private function givenAnEmptyDictionary(): void
    {
        $this->get('database_connection')->executeQuery(<<<SQL
TRUNCATE TABLE pimee_data_quality_insights_text_checker_dictionary;
SQL
        );
    }
}
