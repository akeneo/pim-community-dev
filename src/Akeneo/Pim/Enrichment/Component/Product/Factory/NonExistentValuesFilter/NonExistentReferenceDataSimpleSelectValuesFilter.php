<?php


namespace Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter;

use Akeneo\Pim\Enrichment\Component\Product\Query\GetExistingReferenceDataCodes;
use Akeneo\Pim\Structure\Component\AttributeTypes;

/**
 * @author    Tamara Robichet <tamara.robichet@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NonExistentReferenceDataSimpleSelectValuesFilter implements NonExistentValuesFilter
{
    /** @var GetExistingReferenceDataCodes */
    private $getExistingReferenceDataCodes;

    public function __construct(GetExistingReferenceDataCodes $getExistingReferenceDataCodes)
    {
        $this->getExistingReferenceDataCodes = $getExistingReferenceDataCodes;
    }

    public function filter(OnGoingFilteredRawValues $onGoingFilteredRawValues): OnGoingFilteredRawValues
    {
        $selectValues = $onGoingFilteredRawValues->notFilteredValuesOfTypes(AttributeTypes::REFERENCE_DATA_SIMPLE_SELECT);

        if (empty($selectValues)) {
            return $onGoingFilteredRawValues;
        }

        $optionCodes = $this->getExistingCaseInsensitiveOptionCodes($selectValues);

        $filteredValues = [];

        foreach ($selectValues as $attributeCode => $productValueCollection) {
            foreach ($productValueCollection as $productValues) {
                $simpleSelectValues = [];

                foreach ($productValues['values'] as $channel => $channelValues) {
                    foreach ($channelValues as $locale => $value) {
                        if (!is_array($value)) {
                            $simpleSelectValues[$channel][$locale] = ($optionCodes[$attributeCode] ?? [])[strtolower($value ?? '')] ?? '';
                        }
                    }
                }

                if ($simpleSelectValues !== []) {
                    $filteredValues[AttributeTypes::REFERENCE_DATA_SIMPLE_SELECT][$attributeCode][] = [
                        'identifier' => $productValues['identifier'],
                        'values' => $simpleSelectValues,
                        'properties' => $productValues['properties']
                    ];
                }
            }
        }

        return $onGoingFilteredRawValues->addFilteredValuesIndexedByType($filteredValues);
    }

    private function getExistingCaseInsensitiveOptionCodes(array $selectValues): array
    {
        $options = $this->getOptions($selectValues);

        $existingOptionCodes = [];

        foreach ($options as $attributeCode => $option) {
            foreach ($option as $referenceDataName => $values) {
                $existingOptionCodes[$attributeCode] = $this->getExistingReferenceDataCodes->fromReferenceDataNameAndCodes(
                    $referenceDataName,
                    $values
                );
            }
        }

        $caseInsensitiveOptionsCodes = [];

        foreach ($existingOptionCodes as $attributeCode => $optionCodesForThisAttribute) {
            foreach ($optionCodesForThisAttribute as $optionCodeForThisAttribute) {
                $caseInsensitiveOptionsCodes[$attributeCode][strtolower($optionCodeForThisAttribute)] = $optionCodeForThisAttribute;
            }
        }

        return $caseInsensitiveOptionsCodes;
    }

    private function getOptions(array $selectValues): array
    {
        $optionCodes = [];

        foreach ($selectValues as $attributeCode => $valueCollection) {
            foreach ($valueCollection as $values) {
                $referenceDataName = $values['properties']['reference_data_name'];
                foreach ($values['values'] as $channel => $channelValues) {
                    foreach ($channelValues as $locale => $value) {
                        if (!is_array($value)) {
                            $optionCodes[$attributeCode][$referenceDataName][] = $value;
                        }
                    }
                }
            }
        }

        return $optionCodes;
    }
}
