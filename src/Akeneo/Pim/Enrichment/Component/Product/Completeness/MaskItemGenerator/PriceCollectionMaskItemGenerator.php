<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Completeness\MaskItemGenerator;

use Akeneo\Channel\Infrastructure\Component\Query\PublicApi\FindActivatedCurrenciesInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;

/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PriceCollectionMaskItemGenerator implements MaskItemGeneratorForAttributeType
{
    public function __construct(
        private FindActivatedCurrenciesInterface $findActivatedCurrencies
    ) {
    }

    /**
     * In the PIM, a price collection attribute value is complete if every currency of the current channel is filled.
     * It's not the same behavior as the rest of the application, so we have to do a specific generator for it.
     *
     * Let's take an example of 2 channels,
     * - ecommerce with USD activated
     * - print with USD and EUR activated.
     *
     *
     * To be complete, the generated family masks are respectively the following ones (for a scopable attribute):
     * - price-USD-ecommerce-<all_locales>
     * - price-EUR-USD-print-<all_locales>
     *
     * If the attribute is non scopable, the rule is the same, the required masks will differ depending on the channel:
     * - price-USD-<all_channels>-<all_locales> (required mask for the ecommerce channel)
     * - price-EUR-USD-<all_channels>-<all_locales> (required mask for the ecommerce channel)
     * This means that a non scopable price attribute can be complete for a given channel, but not for the other one
     *
     * In order to avoid a lot of combinations of currencies, potentially leading to a memory overflow,
     * this mask generator "guesses" the required family mask by fetching the activated currencies per channel
     * and build the mask accordingly
     */
    public function forRawValue(string $attributeCode, string $channelCode, string $localeCode, $value): array
    {
        $filledCurrencies = [];
        $currencyCodesByChannel = $this->findActivatedCurrencies->forAllChannelsIndexedByChannelCode();
        foreach ($value as $price) {
            if (isset($price['amount']) && '' !== $price['amount']) {
                $filledCurrencies[] = $price['currency'];
            }
        }
        \sort($filledCurrencies);

        $masks = [];
        if ('<all_channels>' === $channelCode) {
            foreach ($currencyCodesByChannel as $activeCurrenciesForChannel) {
                $masks[] = $this->generateMask(
                    $attributeCode,
                    $channelCode,
                    $localeCode,
                    \array_intersect($filledCurrencies, $activeCurrenciesForChannel)
                );
            }
        } else {
            if (!isset($currencyCodesByChannel[$channelCode])) {
                return [];
            }

            $masks[] = $this->generateMask(
                $attributeCode,
                $channelCode,
                $localeCode,
                \array_intersect($filledCurrencies, $currencyCodesByChannel[$channelCode])
            );
        }

        return $masks;
    }

    public function supportedAttributeTypes(): array
    {
        return [AttributeTypes::PRICE_COLLECTION];
    }

    private function generateMask(
        string $attributeCode,
        string $channelCode,
        string $localeCode,
        array $currencyCodes
    ): string {
        return \sprintf(
            '%s-%s-%s-%s',
            $attributeCode,
            \implode('-', $currencyCodes),
            $channelCode,
            $localeCode
        );
    }
}
