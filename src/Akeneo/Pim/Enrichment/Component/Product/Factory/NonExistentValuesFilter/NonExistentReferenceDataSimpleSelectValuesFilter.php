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

        $referenceDataCodes = $this->getCaseInsensitiveExistingCodes($selectValues);

        $filteredValues = [];

        foreach ($selectValues as $attributeCode => $productValueCollection) {
            foreach ($productValueCollection as $productValues) {
                $simpleSelectValues = [];

                foreach ($productValues['values'] as $channel => $channelValues) {
                    foreach ($channelValues as $locale => $value) {
                        if (!is_array($value)) {
                            $simpleSelectValues[$channel][$locale] = $referenceDataCodes[$attributeCode][strtolower($value)] ?? '';
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

    private function getCaseInsensitiveExistingCodes(array $selectValues): array
    {
        $referenceData = $this->getReferenceData($selectValues);

        $existingReferenceDataCodes = [];

        foreach ($referenceData as $attributeCode => $data) {
            foreach ($data as $referenceDataName => $values) {
                $existingCodes = $this->getExistingReferenceDataCodes->fromReferenceDataNameAndCodes(
                    $referenceDataName,
                    $values
                );
                foreach ($existingCodes as $existingCode) {
                    $existingReferenceDataCodes[$attributeCode][strtolower($existingCode)] = $existingCode;
                }
            }
        }

        return $existingReferenceDataCodes;
    }

    private function getReferenceData(array $selectValues): array
    {
        $referenceDataCodes = [];

        foreach ($selectValues as $attributeCode => $valueCollection) {
            foreach ($valueCollection as $values) {
                $referenceDataName = $values['properties']['reference_data_name'];
                foreach ($values['values'] as $channel => $channelValues) {
                    foreach ($channelValues as $locale => $value) {
                        if (!is_array($value)) {
                            $referenceDataCodes[$attributeCode][$referenceDataName][] = $value;
                        }
                    }
                }
            }
        }

        return $referenceDataCodes;
    }
}
