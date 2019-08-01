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
     * In the case of prices, we need to generate several masks. First, we need to take in account only the filled
     * currencies.
     *
     * For example, if we have 3 channels:
     * - ecommerce, with USD
     * - print, with EUR
     * - tablet, with EUR and USD
     *
     * Then, there is several cases:
     * - The price attribute is non localizable:
     *   - The generated ecommerce mask will be price-USD-<all_channels>-<all_locales>.
     *     The generated price mask needs to contain this mask too if USD is filled.
     *   - The generated print mask will be price-EUR-<all_channels>-<all_locales>.
     *     The generated price mask needs to contain this mask too if EUR is filled.
     *   - The generated tablet mask will be price-EUR-USD-<all_channels>-<all_locales>.
     *     The generated price mask needs to contain this mask if EUR and USD are filled.
     *
     * So this method will return every combinations of the filled currencies; if EUR, GPB and USD are filled, the
     * generated masks will be -, -EUR, -GPB, -USD, -EUR-GPB, -EUR-USD, -GPB-USD and -EUR-GPB-USD, to fit with all
     * the generated family masks.
     */
    public function forRawValue(string $attributeCode, string $channelCode, string $localeCode, $value): array
    {
        $filledCurrencies = [];
        foreach ($value as $price) {
            if (
                is_array($price) &&
                isset($price['currency']) &&
                isset($price['amount']) &&
                null !== $price['amount']
                && '' !== $price['amount']
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

        return $combinations;
    }
}
