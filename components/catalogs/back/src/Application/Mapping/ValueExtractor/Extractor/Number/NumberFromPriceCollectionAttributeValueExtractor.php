<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\Number;

use Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\NumberValueExtractorInterface;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-type CurrencyList array<array-key, array{currency?: string, amount?: mixed}>
 */
final class NumberFromPriceCollectionAttributeValueExtractor implements NumberValueExtractorInterface
{
    public function extract(
        array $product,
        string $code,
        ?string $locale,
        ?string $scope,
        ?array $parameters,
    ): null | float | int {
        $currency = $parameters['currency'] ?? null;
        $currencyValues = $product['raw_values'][$code][$scope][$locale] ?? null;

        if (!\is_string($currency) || !\is_array($currencyValues)) {
            return null;
        }

        /** @var CurrencyList $currencyValues */
        return $this->findCurrencyValue($currency, $currencyValues);
    }

    public function getSupportedSourceType(): string
    {
        return self::SOURCE_TYPE_ATTRIBUTE_PRICE_COLLECTION;
    }

    public function getSupportedTargetType(): string
    {
        return self::TARGET_TYPE_NUMBER;
    }

    public function getSupportedTargetFormat(): ?string
    {
        return null;
    }

    /**
     * @param CurrencyList $currencyValues
     */
    private function findCurrencyValue(string $currency, array $currencyValues): null|int|float
    {
        $value = null;
        foreach ($currencyValues as $currencyValue) {
            $code = $currencyValue['currency'] ?? null;
            if ($code === $currency) {
                /** @var mixed $value */
                $value = $currencyValue['amount'] ?? null;
            }
        }

        if (!\is_scalar($value) || !\is_numeric($value)) {
            return null;
        }

        if (\is_float($value) || \is_int($value)) {
            return $value;
        }

        if ($value === (string) (int) $value) {
            return (int) $value;
        }

        if ($value === (string) (float) $value) {
            return (float) $value;
        }

        if (\number_format((float) $value, 2)  === $value) {
            return (float) $value;
        }


        return null;
    }
}
