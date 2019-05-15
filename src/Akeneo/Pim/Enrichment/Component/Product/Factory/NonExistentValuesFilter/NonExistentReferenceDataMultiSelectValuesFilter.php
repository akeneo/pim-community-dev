<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter;

use Akeneo\Pim\Enrichment\Component\Product\Query\GetExistingReferenceDataCodes;
use Akeneo\Pim\Structure\Component\AttributeTypes;

/**
 * @author    Tamara Robichet <tamara.robichet@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NonExistentReferenceDataMultiSelectValuesFilter implements NonExistentValuesFilter
{
    /** @var GetExistingReferenceDataCodes */
    private $getExistingReferenceDataCodes;

    public function __construct(GetExistingReferenceDataCodes $getExistingReferenceDataCodes)
    {
        $this->getExistingReferenceDataCodes = $getExistingReferenceDataCodes;
    }

    public function filter(OnGoingFilteredRawValues $onGoingFilteredRawValues): OnGoingFilteredRawValues
    {
        $selectValues = $onGoingFilteredRawValues->notFilteredValuesOfTypes(AttributeTypes::REFERENCE_DATA_MULTI_SELECT);

        if (empty($selectValues)) {
            return $onGoingFilteredRawValues;
        }

        $referenceDataCodes = $this->getExistingCaseInsensitiveReferenceDataCodes($selectValues);

        $filteredValues = [];

        foreach ($selectValues as $attributeCode => $productDataList) {
            foreach ($productDataList as $productData) {
                $multiSelectValues = [];

                foreach ($productData['values'] as $channel => $valuesIndexedByLocale) {
                    foreach ($valuesIndexedByLocale as $locale => $value) {
                        if (is_array($value)) {
                            $multiSelectValues[$channel][$locale] = $this->arrayIntersectCaseInsensitive($value, $referenceDataCodes[$attributeCode] ?? []);
                        }
                    }
                }

                if ($multiSelectValues !== []) {
                    $filteredValues[AttributeTypes::REFERENCE_DATA_MULTI_SELECT][$attributeCode][] = [
                        'identifier' => $productData['identifier'],
                        'values' => $multiSelectValues,
                        'properties' => $productData['properties']
                    ];
                }
            }
        }

        return $onGoingFilteredRawValues->addFilteredValuesIndexedByType($filteredValues);
    }

    private function getExistingCaseInsensitiveReferenceDataCodes(array $selectValues): array
    {
        $referenceData = $this->getReferenceData($selectValues);

        $existingReferenceDataCodes = [];

        foreach ($referenceData as $attributeCode => $data) {
            foreach ($data as $referenceDataName => $values) {
                $existingReferenceDataCodes[$attributeCode] = $this->getExistingReferenceDataCodes->fromReferenceDataNameAndCodes(
                    $referenceDataName,
                    $values
                );
            }
        }

        $caseInsensitiveReferenceDataCodes = [];

        foreach ($existingReferenceDataCodes as $attributeCode => $referenceDataCodesForThisAttribute) {
            foreach ($referenceDataCodesForThisAttribute as $referenceDataCodeForThisAttribute) {
                $caseInsensitiveReferenceDataCodes[$attributeCode][strtolower($referenceDataCodeForThisAttribute)] = $referenceDataCodeForThisAttribute;
            }
        }

        return $caseInsensitiveReferenceDataCodes;
    }

    private function getReferenceData(array $selectValues): array
    {
        $referenceData = [];

        foreach ($selectValues as $attributeCode => $valueCollection) {
            foreach ($valueCollection as $values) {
                $referenceDataName = $values['properties']['reference_data_name'];
                foreach ($values['values'] as $channel => $channelValues) {
                    foreach ($channelValues as $locale => $values) {
                        foreach ($values as $value) {
                            $referenceData[$attributeCode][$referenceDataName][] = $value;
                        }
                    }
                }
            }
        }

        return $referenceData;
    }

    private function arrayIntersectCaseInsensitive(array $givenOptionCodes, array $existentOptionCodesIndexedInsensitive): array
    {
        $result = [];

        if (empty($existentOptionCodesIndexedInsensitive)) {
            return [];
        }

        foreach ($givenOptionCodes as $optionCode) {
            if (isset($existentOptionCodesIndexedInsensitive[strtolower($optionCode ?? '')])) {
                $result[] = $existentOptionCodesIndexedInsensitive[strtolower($optionCode)];
            }
        }

        return $result;
    }
}
