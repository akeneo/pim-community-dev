<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Test\Akeneo\Pim\Automation\DataQualityInsights\Integration\Persistence\Query\Dictionary;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Dictionary\GetDictionaryLastUpdateDateByLocaleQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Dictionary\CachedGetDictionaryLastUpdateDateByLocaleQuery;
use Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\AttributeGrid\TestCase;
use Doctrine\DBAL\Types\Types;

final class CachedGetDictionaryLastUpdateDateByLocaleQueryIntegration extends TestCase
{
    public function test_it_retrieves_the_last_update_date_by_locale(): void
    {
        $enUsLastUpdateDate = new \DateTimeImmutable('2022-06-03 12:07:51');
        $frFrLastUpdateDate = new \DateTimeImmutable('2022-06-02 11:43:19');

        $this->givenAnEmptyDictionary();
        $this->givenAWordWithoutUpdateDate('SKU', 'en_US');
        $this->givenADictionaryWord('Akeneo', 'en_US', true, $enUsLastUpdateDate->modify('-1 SECOND'));
        $this->givenADictionaryWord('Ziggy', 'en_US', false, $enUsLastUpdateDate);
        $this->givenADictionaryWord('Akeneo', 'fr_FR', true, $frFrLastUpdateDate);
        $this->givenADictionaryWord('Ziggy', 'fr_FR', true, $frFrLastUpdateDate->modify('-1 HOUR'));

        $this->assertEquals($enUsLastUpdateDate, $this->getQuery()->execute(new LocaleCode('en_US')), 'Expected date for en_US');
        $this->assertEquals($frFrLastUpdateDate, $this->getQuery()->execute(new LocaleCode('fr_FR')), 'Expected date for fr_FR');
        $this->assertNull($this->getQuery()->execute(new LocaleCode('de_DE')));
    }

    private function getQuery(): GetDictionaryLastUpdateDateByLocaleQueryInterface
    {
        return $this->get(CachedGetDictionaryLastUpdateDateByLocaleQuery::class);
    }

    private function givenADictionaryWord(string $word, string $locale, bool $enabled, \DateTimeImmutable $updatedAt): void
    {
        $query = <<<SQL
INSERT INTO pimee_data_quality_insights_text_checker_dictionary (locale_code, word, enabled, updated_at)
VALUES (:locale, :word, :enabled, :updatedAt);
SQL;

        $this->get('database_connection')->executeQuery(
            $query,
            [
                'locale' => $locale,
                'word' => $word,
                'enabled' => $enabled,
                'updatedAt' => $updatedAt,
            ],
            [
                'locale' => Types::STRING,
                'word' => Types::STRING,
                'enabled' => Types::BOOLEAN,
                'updatedAt' => Types::DATETIME_IMMUTABLE,
            ]
        );
    }

    private function givenAWordWithoutUpdateDate(string $word, string $locale): void
    {
        $query = <<<SQL
INSERT INTO pimee_data_quality_insights_text_checker_dictionary (locale_code, word)
VALUES (:locale, :word);
SQL;

        $this->get('database_connection')->executeQuery(
            $query,
            ['locale' => $locale, 'word' => $word,],
        );
    }

    private function givenAnEmptyDictionary(): void
    {
        $this->get('database_connection')->executeQuery('TRUNCATE TABLE pimee_data_quality_insights_text_checker_dictionary;');
    }
}
