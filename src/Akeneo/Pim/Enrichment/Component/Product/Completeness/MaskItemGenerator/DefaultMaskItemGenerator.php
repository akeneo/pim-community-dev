<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Completeness\MaskItemGenerator;

use Akeneo\Pim\Structure\Component\AttributeTypes;

/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DefaultMaskItemGenerator implements MaskItemGeneratorForAttributeType
{
    public function forRawValue(string $attributeCode, string $channelCode, string $localeCode, $value): array
    {
        return [
            sprintf(
                '%s-%s-%s',
                $attributeCode,
                $channelCode,
                $localeCode
            )
        ];
    }

    public function supportedAttributeTypes(): array
    {
        return [
            AttributeTypes::BOOLEAN,
            AttributeTypes::DATE,
            AttributeTypes::FILE,
            AttributeTypes::IDENTIFIER,
            AttributeTypes::IMAGE,
            AttributeTypes::NUMBER,
            AttributeTypes::OPTION_MULTI_SELECT,
            AttributeTypes::OPTION_SIMPLE_SELECT,
            AttributeTypes::TEXTAREA,
            AttributeTypes::TEXT,
            AttributeTypes::REFERENCE_DATA_MULTI_SELECT,
            AttributeTypes::REFERENCE_DATA_SIMPLE_SELECT,
            AttributeTypes::REFERENCE_ENTITY_SIMPLE_SELECT,
            AttributeTypes::REFERENCE_ENTITY_COLLECTION,
            AttributeTypes::ASSET_COLLECTION,
            AttributeTypes::LEGACY_ASSET_COLLECTION,
        ];
    }
}
