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
class NonExistentSelectValuesFilter implements NonExistentValuesFilter
{
    /** @var GetExistingAttributeOptionCodes */
    private $getExistingAttributeOptionCodes;

    public function __construct(GetExistingAttributeOptionCodes $getExistingAttributeOptionCodes)
    {
        $this->getExistingAttributeOptionCodes = $getExistingAttributeOptionCodes;
    }

    public function filter(OnGoingFilteredRawValues $onGoingFilteredRawValues): OnGoingFilteredRawValues
    {
        $selectValues = $onGoingFilteredRawValues->notFilteredValuesOfTypes(
            AttributeTypes::OPTION_SIMPLE_SELECT,
            AttributeTypes::OPTION_MULTI_SELECT
        );

        if (empty($selectValues)) {
            return $onGoingFilteredRawValues;
        }

        $optionCodes = [];

        foreach ($selectValues as $attributeCode => $valueCollection) {
            foreach ($valueCollection as $values) {
                foreach ($values['values'] as $channel => $channelValues) {
                    foreach ($channelValues as $locale => $value) {
                        if (is_array($value)) {
                            foreach ($value as $optionCode) {
                                $optionCodes[$attributeCode][] = $optionCode;
                            }
                        } else {
                            $optionCodes[$attributeCode][] = $value;
                        }
                    }
                }
            }
        }

        $uniqueOptionCodes = [];
        foreach ($optionCodes as $attributeCode => $optionCodeForThisAttribute) {
            $uniqueOptionCodes[$attributeCode] = array_unique($optionCodeForThisAttribute);
        }

        $existingOptionCodes = $this->getExistingAttributeOptionCodes->fromOptionCodesByAttributeCode($uniqueOptionCodes);
        $caseUnsensitiveOptionsCodes = [];
        foreach ($existingOptionCodes as $attributeCode => $optionCodesForThisAttribute) {
            foreach ($optionCodesForThisAttribute as $optionCodeForThisAttribute) {
                $caseUnsensitiveOptionsCodes[$attributeCode][strtolower($optionCodeForThisAttribute)] = $optionCodeForThisAttribute;
            }
        }

        $filteredValues = [];

        foreach ($selectValues as $attributeCode => $productValueCollection) {
            foreach ($productValueCollection as $productValues) {
                $multiSelectValues = [];
                $simpleSelectValues = [];
                foreach ($productValues['values'] as $channel => $channelValues) {
                    foreach ($channelValues as $locale => $value) {
                        // MULTI_SELECT ATTRIBUTE
                        if (is_array($value)) {
                            $multiSelectValues[$channel][$locale] = $this->arrayIntersectCaseInsensitive($value, $caseUnsensitiveOptionsCodes[$attributeCode] ?? []);
                        // SIMPLE SELECT ATTRIBUTE
                        } else {
                            $simpleSelectValues[$channel][$locale] = ($caseUnsensitiveOptionsCodes[$attributeCode] ?? [])[strtolower($value)] ?? '';
                        }
                    }
                }
                if ($multiSelectValues !== []) {
                    $filteredValues[AttributeTypes::OPTION_MULTI_SELECT][$attributeCode][] = [
                        'identifier' => $productValues['identifier'],
                        'values' => $multiSelectValues,
                    ];
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

    private function arrayIntersectCaseInsensitive(array $givenOptionCodes, array $existentOptionCodesIndexedUnsensitive): array
    {
        $result = [];

        if (empty($existentOptionCodesIndexedUnsensitive)) {
            return [];
        }

        foreach ($givenOptionCodes as $optionCode) {
            if (isset($existentOptionCodesIndexedUnsensitive[strtolower($optionCode)])) {
                $result[] = $existentOptionCodesIndexedUnsensitive[strtolower($optionCode)];
            }
        }

        return $result;
    }
}
