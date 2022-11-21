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
class NonExistentMultiSelectValuesFilter implements NonExistentValuesFilter
{
    public function __construct(private GetExistingAttributeOptionCodes $getExistingAttributeOptionCodes)
    {
    }

    public function filter(OnGoingFilteredRawValues $onGoingFilteredRawValues): OnGoingFilteredRawValues
    {
        $selectValues = $onGoingFilteredRawValues->notFilteredValuesOfTypes(AttributeTypes::OPTION_MULTI_SELECT);

        if (empty($selectValues)) {
            return $onGoingFilteredRawValues;
        }

        $optionCodes = $this->getExistingOptionCodes($selectValues);

        $filteredValues = [];
        foreach ($selectValues as $attributeCode => $productValueCollection) {
            $existingCodes = $optionCodes[$attributeCode] ?? [];
            foreach ($productValueCollection as $productValues) {
                $multiSelectValues = [];

                foreach ($productValues['values'] as $channel => $channelValues) {
                    foreach ($channelValues as $locale => $values) {
                        if (\is_array($values)) {
                            $multiSelectValues[$channel][$locale] = \array_values(\array_intersect($existingCodes, $values));
                        }
                    }
                }

                if ($multiSelectValues !== []) {
                    $filteredValues[AttributeTypes::OPTION_MULTI_SELECT][$attributeCode][] = [
                        'identifier' => $productValues['identifier'],
                        'values' => $multiSelectValues,
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
                        if (\is_array($value)) {
                            foreach ($value as $optionCode) {
                                $optionCodes[$attributeCode][] = $optionCode;
                            }
                        }
                    }
                }
            }
        }

        $uniqueOptionCodes = [];
        foreach ($optionCodes as $attributeCode => $optionCodeForThisAttribute) {
            $uniqueOptionCodes[$attributeCode] = \array_values(\array_unique($optionCodeForThisAttribute));
        }

        return $uniqueOptionCodes;
    }

    private function arrayIntersectCaseInsensitive(array $givenOptionCodes, array $existentOptionCodesIndexedInsensitive): array
    {
        $result = [];

        if (empty($existentOptionCodesIndexedInsensitive)) {
            return [];
        }

        foreach ($givenOptionCodes as $optionCode) {
            if (isset($existentOptionCodesIndexedInsensitive[\strtolower($optionCode ?? '')])) {
                $result[] = $existentOptionCodesIndexedInsensitive[\strtolower($optionCode)];
            }
        }

        return \array_unique($result);
    }
}
