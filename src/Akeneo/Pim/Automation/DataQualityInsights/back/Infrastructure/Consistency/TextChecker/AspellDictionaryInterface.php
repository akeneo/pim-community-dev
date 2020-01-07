<?php


namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Consistency\TextChecker;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Exception\DictionaryNotFoundException;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Dictionary;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LanguageCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;

interface AspellDictionaryInterface
{
    /**
     * @throws \RuntimeException
     */
    public function persistDictionaryToSharedFilesystem(Dictionary $dictionary, LanguageCode $languageCode): void;

    /**
     * @throws DictionaryNotFoundException
     */
    public function getUpToDateLocalDictionaryAbsoluteFilePath(LocaleCode $localeCode): string;

    public function getSharedDictionaryTimestamp(LanguageCode $languageCode): ?int;
}
