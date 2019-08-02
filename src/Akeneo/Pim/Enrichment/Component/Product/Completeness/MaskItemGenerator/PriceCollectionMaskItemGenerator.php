<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Completeness\MaskItemGenerator;

use Akeneo\Pim\Structure\Component\AttributeTypes;

/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PriceCollectionMaskItemGenerator implements MaskItemGeneratorForAttributeType
{
    /**
     * In the PIM, a price collection attribute value is complete if every currency of the current channel is filled.
     * It's not the same behavior of the rest of the application, so we have to do a specific generator for it.
     *
     * Let's take an example of 2 channels,
     * - ecommerce with USD activated
     * - print with USD and EUR activated.
     * To be complete, the generated family masks are respectively the following ones:
     * - price-USD-channel-locale
     * - price-EUR-USD-channel-locale
     *
     * For the scopable price collections, the behavior remains simple as the user can only fill the USD in the
     * ecommerce channel, and can fill USD and EUR in the print channel. The generated masks can respectively be
     * - price--channel-locale (in case of nothing filled) or price-EUR-channel-locale (in case of EUR filled), which
     *   respects the generated family mask.
     * - price--channel-locale, price-EUR-channel-locale or price-USD-channel-locale (in case of missing data), or
     *   price-EUR-USD-channel-locale (which respects the generated family mask).
     *
     * For the non scopable price collections, the behavior is more complex. The data is the same for ecommerce or
     * print, so the user is able to set USD data in every channel, including ecommerce channel (even if USD is not
     * part of the ecommerce channel).
     * If the user fills USD and EUR, this attribute value has to be complete in ecommerce (because USD is filled) and
     * print (because EUR and USD are filled).
     * As the price collection is not scopable, the generated mask(s) are the same regarding the both channels.
     * So we need to return price-EUR-channel-locale (for any channel having only EUR currency),
     * price-USD-channel-locale (for any channel having only USD currency), and price-EUR-USD-channel-locale (for any
     * channel having both EUR and USD currencies).
     *
     * With 3 currencies, if EUR, GPB and USD are filled, the generated masks will be -EUR, -GPB, -USD, -EUR-GPB,
     * -EUR-USD, -GPB-USD and -EUR-GPB-USD.
     *
     * In conclusion, we need to return each combination of filled currencies, to be able to match any channel
     * currencies.
     */
    public function forRawValue(string $attributeCode, string $channelCode, string $localeCode, $value): array
    {
        $filledCurrencies = [];
        foreach ($value as $price) {
            if (
                isset($price['amount']) &&
                '' !== $price['amount']
            ) {
                $filledCurrencies[] = $price['currency'];
            }
        }
        sort($filledCurrencies);

        $result = [];
        foreach ($this->getCurrencyCombinations($filledCurrencies) as $currencyCombination) {
            $result[] = sprintf('%s-%s-%s-%s',
                $attributeCode,
                join('-', $currencyCombination),
                $channelCode,
                $localeCode
            );
        }

        return $result;
    }

    public function supportedAttributeTypes(): array
    {
        return [AttributeTypes::PRICE_COLLECTION];
    }

    private function getCurrencyCombinations($currencies)
    {
        $combinations = [[]];

        foreach ($currencies as $currency) {
            foreach ($combinations as $combination) {
                array_push($combinations, array_merge($combination, [$currency]));
            }
        }
        unset($combinations[0]);

        return $combinations;
    }
}
