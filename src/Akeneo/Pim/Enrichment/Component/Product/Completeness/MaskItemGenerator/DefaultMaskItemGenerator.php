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
        $result = [];
        if (null !== $value || (is_array($value) && count($value) > 0)) {
            $mask = sprintf(
                '%s-%s-%s',
                $attributeCode,
                $channelCode,
                $localeCode
            );
            $result = [$mask];
        }

        return $result;
    }

    private function valueIsFilled($value): bool
    {
        if (null === $value) {
            return false;
        }

        if ('' === $value) {
            return false;
        }

        if (is_array($value)) {
            foreach ($value as $subValue) {
                if ($this->valueIsFilled($subValue)) {
                    return true;
                }
            }

            return false;
        }

        return true;
    }

    public function supportedAttributeTypes(): array
    {
        // TODO [review] Don't know if this has to be injected via DI. They already contains EE ones, but I don't know
        //      how difficult it can be for extensibility.
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
            AttributeTypes::ASSET_SINGLE_LINK,
            AttributeTypes::ASSET_MULTIPLE_LINK,
        ];
    }
}
