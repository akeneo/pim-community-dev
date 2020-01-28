<?php
declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Consistency\TextChecker;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Clock;
use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Consistency\DictionaryGenerator;
use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Consistency\DictionarySource;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Filter\LocaleCodeByLanguageCodeFilterIterator;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\LocaleCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetAllActivatedLocalesQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LanguageCode;

final class AspellDictionaryGenerator implements DictionaryGenerator
{
    private const NUMBER_OF_DAYS_BETWEEN_DICTIONARY_NEW_GENERATION = 30;

    /** @var AspellDictionaryInterface */
    private $aspellDictionary;

    /** @var GetAllActivatedLocalesQueryInterface */
    private $allActivatedLocalesQuery;

    /** @var Clock */
    private $clock;

    /** @var bool */
    private $ignoreCheckTimestamp;

    public function __construct(
        AspellDictionaryInterface $aspellDictionary,
        GetAllActivatedLocalesQueryInterface $allActivatedLocalesQuery,
        Clock $clock
    ) {
        $this->aspellDictionary = $aspellDictionary;
        $this->allActivatedLocalesQuery = $allActivatedLocalesQuery;
        $this->clock = $clock;
        $this->ignoreCheckTimestamp = false;
    }

    public function generate(DictionarySource $dictionarySource): void
    {
        foreach ($this->getFilteredLocaleCollectionsBySupportedLanguageCode() as $languageCode => $localeCollection) {
            $languageCode = new LanguageCode($languageCode);

            if (false === $this->isGenerationAllowed($languageCode)) {
                continue;
            }

            $dictionary = $dictionarySource->getDictionary($localeCollection);

            $this->aspellDictionary->persistDictionaryToSharedFilesystem($dictionary, $languageCode);
        }
    }

    private function getFilteredLocaleCollectionsBySupportedLanguageCode(): \Generator
    {
        $allActivatedLocales = $this->allActivatedLocalesQuery->execute();

        foreach (SupportedPrefixLocaleChecker::AVAILABLE_LANGUAGE_CODE as $supportedLanguageCode) {
            $filteredLocalesCode = iterator_to_array(
                new LocaleCodeByLanguageCodeFilterIterator(
                    $allActivatedLocales->getIterator(),
                    new LanguageCode($supportedLanguageCode)
                )
            );

            if (count($filteredLocalesCode) === 0) {
                continue;
            }

            yield $supportedLanguageCode => new LocaleCollection($filteredLocalesCode);
        }
    }

    public function ignoreCheckTimestamp(): self
    {
        $this->ignoreCheckTimestamp = true;

        return $this;
    }

    private function isGenerationAllowed(LanguageCode $languageCode): bool
    {
        if (true === $this->ignoreCheckTimestamp) {
            return true;
        }

        $timestamp = $this->aspellDictionary->getSharedDictionaryTimestamp($languageCode);

        if (null === $timestamp) {
            return true;
        }

        $fileDate = $this->clock->fromTimestamp($timestamp);
        $now = $this->clock->getCurrentTime();
        $interval = $fileDate->diff($now);

        if ($interval->format('%a') >= self::NUMBER_OF_DAYS_BETWEEN_DICTIONARY_NEW_GENERATION) {
            return true;
        }

        return false;
    }
}
