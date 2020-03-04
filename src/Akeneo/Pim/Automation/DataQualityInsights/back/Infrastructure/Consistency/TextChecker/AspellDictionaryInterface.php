<?php


namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Consistency\TextChecker;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Exception\UnableToRetrieveDictionaryException;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Dictionary;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LanguageCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Mekras\Speller;

interface AspellDictionaryInterface
{
    /**
     * @throws \RuntimeException
     */
    public function persistDictionaryToSharedFilesystem(Dictionary $dictionary, LanguageCode $languageCode): void;

    /**
     * @throws UnableToRetrieveDictionaryException
     */
    public function getUpToDateSpellerDictionary(LocaleCode $localeCode): ?Speller\Dictionary;

    public function getSharedDictionaryTimestamp(LanguageCode $languageCode): ?int;
}
