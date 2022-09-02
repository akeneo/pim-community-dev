<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption\GetExistingAttributeOptionCodes;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NonExistentSimpleSelectValuesFilter implements NonExistentValuesFilter
{
    public function __construct(private GetExistingAttributeOptionCodes $getExistingAttributeOptionCodes)
    {
    }

    public function filter(OnGoingFilteredRawValues $onGoingFilteredRawValues): OnGoingFilteredRawValues
    {
        $selectValues = $onGoingFilteredRawValues->notFilteredValuesOfTypes(AttributeTypes::OPTION_SIMPLE_SELECT);
        if (empty($selectValues)) {
            return $onGoingFilteredRawValues;
        }

        $optionCodes = $this->getExistingOptionCodes($selectValues);

        $filteredValues = [];
        foreach ($selectValues as $attributeCode => $productValueCollection) {
            $existingCodes = $optionCodes[$attributeCode] ?? [];
            foreach ($productValueCollection as $productValues) {
                $simpleSelectValues = [];
                foreach ($productValues['values'] as $channel => $channelValues) {
                    foreach ($channelValues as $locale => $value) {
                        if (!\is_array($value)) {
                            $simpleSelectValues[$channel][$locale] = \in_array($value, $existingCodes) ? $value : '';
                        }
                    }
                }

                if ($simpleSelectValues !== []) {
                    $filteredValues[AttributeTypes::OPTION_SIMPLE_SELECT][$attributeCode][] = [
                        'identifier' => $productValues['identifier'],
                        'values' => $simpleSelectValues,
                    ];
                }
            }
        }

        return $onGoingFilteredRawValues->addFilteredValuesIndexedByType($filteredValues);
    }

    private function getExistingOptionCodes(array $selectValues): array
    {
        $optionCodes = $this->getOptionCodes($selectValues);
        $existingOptionCodes = $this->getExistingAttributeOptionCodes->fromOptionCodesByAttributeCode($optionCodes);

        foreach ($optionCodes as $attributeCode => $optionCodesForThisAttribute) {
            $existingOptionCodesForAttribute = $existingOptionCodes[$attributeCode] ?? [];
            if (empty($existingOptionCodesForAttribute)) {
                $optionCodes[$attributeCode] = [];
                continue;
            }

            $existingOptionCodesForAttribute = \array_map('strtolower', $existingOptionCodesForAttribute);
            $optionCodes[$attributeCode] = \array_filter(
                $optionCodesForThisAttribute,
                fn ($code) => \in_array(\strtolower($code), $existingOptionCodesForAttribute)
            );
        }

        return $optionCodes;
    }

    private function getOptionCodes(array $selectValues): array
    {
        $optionCodes = [];

        foreach ($selectValues as $attributeCode => $valueCollection) {
            foreach ($valueCollection as $values) {
                foreach ($values['values'] as $channel => $channelValues) {
                    foreach ($channelValues as $locale => $value) {
                        if (!\is_array($value)) {
                            $optionCodes[$attributeCode][] = $value;
                        }
                    }
                }
            }
        }

        $uniqueOptionCodes = [];
        foreach ($optionCodes as $attributeCode => $optionCodeForThisAttribute) {
            $uniqueOptionCodes[$attributeCode] = \array_unique($optionCodeForThisAttribute);
        }

        return $uniqueOptionCodes;
    }
}
