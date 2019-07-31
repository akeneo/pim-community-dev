<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Completeness\MaskItemGenerator;

use Akeneo\Pim\Structure\Component\AttributeTypes;

/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MetricMaskItemGenerator implements MaskItemGeneratorForAttributeType
{
    public function forRawValue(string $attributeCode, string $channelCode, string $localeCode, $value): array
    {
        if (
            !isset($value['amount']) ||
            '' === $value['amount'] ||
            !isset($value['unit']) ||
            '' === $value['unit'] ||
            !isset($value['base_data']) ||
            '' === $value['base_data'] ||
            !isset($value['base_unit']) ||
            '' === $value['base_unit']
        ) {
            return [];
        }

        return [
            sprintf('%s-%s-%s',
                $attributeCode,
                $channelCode,
                $localeCode
            )
        ];
    }

    public function supportedAttributeTypes(): array
    {
        return [AttributeTypes::METRIC];
    }
}
