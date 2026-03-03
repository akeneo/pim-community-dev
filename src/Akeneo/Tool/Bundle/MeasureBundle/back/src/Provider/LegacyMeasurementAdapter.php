<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\Provider;

use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamily;

/**
 * @author    Valentin Dijkstra <valentin.dijkstra@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
class LegacyMeasurementAdapter
{
    public function adapts(MeasurementFamily $measurementFamily)
    {
        $normalizedMeasurementFamily = $measurementFamily->normalize();

        return [
            $normalizedMeasurementFamily['code'] => [
                'standard' => $normalizedMeasurementFamily['standard_unit_code'],
                'units' => $this->adaptUnits($normalizedMeasurementFamily)
            ]
        ];
    }

    private function adaptUnits(array $normalizedMeasurementFamily): array
    {
        $result = [];
        foreach ($normalizedMeasurementFamily['units'] as $unit) {
            $result[$unit['code']] = [
                'convert' => $this->adaptOperations($unit),
                'symbol' => $unit['symbol']
            ];
        }

        return $result;
    }

    private function adaptOperations(array $unit): array
    {
        return array_map(static fn (array $operation) => [
            $operation['operator'] => $operation['value']
        ], $unit['convert_from_standard']);
    }
}
