<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\DictionaryWord;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;

/**
 * @author Olivier Pontier <olivier.pontier@akeneo.com>
 */
interface TextCheckerDictionaryRepositoryInterface
{
    public function findByLocaleCode(LocaleCode $localeCode): array;

    public function paginatedSearch(LocaleCode $localeCode, int $page, int $itemsPerPage, string $search): array;

    /**
     * @param DictionaryWord[] $words
     *
     * @return DictionaryWord[] words that exist in the dictionary for the given locale.
     */
    public function filterExistingWords(LocaleCode $localeCode, array $words): array;

    public function save(Write\TextCheckerDictionaryWord $dictionaryWord): void;

    /**
     * @param Write\TextCheckerDictionaryWord[] $dictionaryWords
     */
    public function saveAll(array $dictionaryWords): void;

    public function deleteWord(int $wordId): void;
}
