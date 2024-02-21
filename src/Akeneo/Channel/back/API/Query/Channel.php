<?php

namespace Akeneo\Channel\API\Query;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Channel
{
    /**
     * @param array<string> $localeCodes
     * @param array<string> $activeCurrencies
     */
    public function __construct(
        private string $code,
        private array $localeCodes,
        private LabelCollection $labels,
        private array $activeCurrencies,
        private ConversionUnitCollection $conversionUnits,
    ) {
    }

    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @return string[]
     */
    public function getLocaleCodes(): array
    {
        return $this->localeCodes;
    }

    public function getLabels(): LabelCollection
    {
        return $this->labels;
    }

    /**
     * @return string[]
     */
    public function getActiveCurrencies(): array
    {
        return $this->activeCurrencies;
    }

    public function isLocaleActive(string $localeCode): bool
    {
        return in_array($localeCode, $this->localeCodes);
    }

    public function isCurrencyActive(string $currencyCode): bool
    {
        return in_array($currencyCode, $this->activeCurrencies);
    }

    public function getConversionUnits(): ConversionUnitCollection
    {
        return $this->conversionUnits;
    }
}
