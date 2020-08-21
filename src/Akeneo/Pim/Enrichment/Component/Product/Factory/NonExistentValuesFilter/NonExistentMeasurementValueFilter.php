<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter;

use Akeneo\Channel\Component\Query\FindActivatedCurrenciesInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class NonExistentMeasurementValueFilter implements NonExistentValuesFilter
{
    public function filter(OnGoingFilteredRawValues $onGoingFilteredRawValues): OnGoingFilteredRawValues
    {
        $metricValues = $onGoingFilteredRawValues->notFilteredValuesOfTypes(AttributeTypes::METRIC);

        if (empty($metricValues)) {
            return $onGoingFilteredRawValues;
        }

        return $onGoingFilteredRawValues->addFilteredValuesIndexedByType($this->existingMetricValues($metricValues));
    }

    /**
     * This method is a little bit complicated, just keep in mind that it returns values
     * of type metric without the --non activated locales-- <= ?
     */
    private function existingMetricValues(array $metricValues): array
    {
        $filteredValues = [];
        $existingMeasurements = []; // TODO: Query existing measurements

        foreach ($metricValues as $attributeCode => $productListData) {
            foreach ($productListData as $productData) {
                $metricValues = [];
                foreach ($productData['values'] as $channel => $valuesIndexedByLocale) {
                    foreach ($valuesIndexedByLocale as $locale => $value) {
                        if (
                            isset($value['amount'], $value['unit'])
                            && $this->unitExists($value, $existingMeasurements)
                        ) {
                            $metricValues[$channel][$locale] = $value;
                        }
                    }
                }
                if (!empty($metricValues)) {
                    $filteredValues[AttributeTypes::METRIC][$attributeCode][] = [
                        'identifier' => $productData['identifier'],
                        'values' => $metricValues,
                        'properties' => $productData['properties'] ?? []
                    ];
                }
            }
        }

        return $filteredValues;
    }

    private function unitExists(array $value, array $existingMeasurements): bool
    {
        // TODO:
        // Write better implementation
        // It should check on measurement unit AND measurement family
        // $value['unit'] AND $value['family']

        return true;
    }
}
