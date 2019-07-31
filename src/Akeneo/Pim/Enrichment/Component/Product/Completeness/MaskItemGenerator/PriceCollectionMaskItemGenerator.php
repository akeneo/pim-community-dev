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
    public function forRawValue(string $attributeCode, string $channelCode, string $localeCode, $value): array
    {
        $filledCurrencies = [];
        foreach ($value as $price) {
            if (
                is_array($price) &&
                isset($price['amount']) &&
                '' !== $price['amount']
            ) {
                $filledCurrencies[] = $price['currency'];
            }
        }
        sort($filledCurrencies);

        return [
            sprintf('%s-%s-%s-%s',
                $attributeCode,
                join('-', $filledCurrencies),
                $channelCode,
                $localeCode
            )
        ];
    }

    public function supportedAttributeTypes(): array
    {
        return [AttributeTypes::PRICE_COLLECTION];
    }
}
