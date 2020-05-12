<?php
declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Aspell;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Clock;
use Akeneo\Pim\Automation\DataQualityInsights\Application\Spellcheck\DictionaryGenerator;
use Akeneo\Pim\Automation\DataQualityInsights\Application\Spellcheck\DictionarySource;
use Akeneo\Pim\Automation\DataQualityInsights\Application\Spellcheck\SupportedLocaleValidator;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LanguageCode;

final class AspellDictionaryGenerator implements DictionaryGenerator
{
    private const NUMBER_OF_DAYS_BETWEEN_DICTIONARY_NEW_GENERATION = 30;

    /** @var AspellDictionaryInterface */
    private $aspellDictionary;

    /** @var Clock */
    private $clock;

    /** @var bool */
    private $ignoreCheckTimestamp;
    /**
     * @var SupportedLocaleValidator
     */
    private $supportedLocaleValidator;

    public function __construct(
        AspellDictionaryInterface $aspellDictionary,
        SupportedLocaleValidator $supportedLocaleValidator,
        Clock $clock
    ) {
        $this->aspellDictionary = $aspellDictionary;
        $this->clock = $clock;
        $this->ignoreCheckTimestamp = false;
        $this->supportedLocaleValidator = $supportedLocaleValidator;
    }

    public function generate(DictionarySource $dictionarySource): void
    {
        foreach ($this->supportedLocaleValidator->getSupportedLocaleCollection() as $languageCode => $localeCollection) {
            $languageCode = new LanguageCode($languageCode);

            if (false === $this->isGenerationAllowed($languageCode)) {
                continue;
            }

            $dictionary = $dictionarySource->getDictionary($localeCollection);

            $this->aspellDictionary->persistDictionaryToSharedFilesystem($dictionary, $languageCode);
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
