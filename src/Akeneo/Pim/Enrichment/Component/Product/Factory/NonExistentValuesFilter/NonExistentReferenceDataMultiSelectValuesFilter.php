<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter;

use Akeneo\Pim\Enrichment\Component\Product\Query\GetExistingReferenceDataCodes;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;

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

        $existingReferenceDataCodes = $this->getExistingCodes($selectValues);

        $filteredValues = [];

        foreach ($selectValues as $attributeCode => $productDataList) {
            foreach ($productDataList as $productData) {
                $multiSelectValues = [];

                foreach ($productData['values'] as $channel => $valuesIndexedByLocale) {
                    foreach ($valuesIndexedByLocale as $locale => $values) {
                        if (\is_array($values)) {
                            $multiSelectValues[$channel][$locale] = array_values(array_intersect($values, $existingReferenceDataCodes[$attributeCode] ?? []));
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

    private function getExistingCodes(array $selectValues): array
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

        return $existingReferenceDataCodes;
    }

    private function getReferenceData(array $selectValues): array
    {
        $referenceData = [];

        foreach ($selectValues as $attributeCode => $valueCollection) {
            foreach ($valueCollection as $values) {
                $referenceDataName = $values['properties']['reference_data_name'];
                foreach ($values['values'] as $channel => $channelValues) {
                    foreach ($channelValues as $locale => $values) {
                        if (!\is_array($values)) {
                            throw InvalidPropertyTypeException::arrayExpected(
                                $attributeCode,
                                static::class,
                                $values
                            );
                        }

                        foreach ($values as $value) {
                            if (!\is_scalar($value)) {
                                throw InvalidPropertyTypeException::validArrayStructureExpected(
                                    $attributeCode,
                                    sprintf('one of the "%s" values is not a scalar', $attributeCode),
                                    static::class,
                                    $values
                                );
                            }

                            $referenceData[$attributeCode][$referenceDataName][] = $value;
                        }
                    }
                }
            }
        }

        return $referenceData;
    }
}
