<?php

namespace Akeneo\Channel\API\Query;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class Channel
{
    public function __construct(
        private string $code,
        private array $localeCodes,
        private LabelCollection $labels,
        private array $activatedCurrencies
    ) {
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getLocaleCodes(): array
    {
        return $this->localeCodes;
    }

    public function getLabels(): LabelCollection
    {
        return $this->labels;
    }

    public function getActivatedCurrencies(): array
    {
        return $this->activatedCurrencies;
    }

    public function isLocaleActive(string $localeCode): bool
    {
        return in_array($localeCode, $this->localeCodes);
    }

    public function isActivatedCurrency(string $currencyCode): bool
    {
        return in_array($currencyCode, $this->activatedCurrencies);
    }
}
