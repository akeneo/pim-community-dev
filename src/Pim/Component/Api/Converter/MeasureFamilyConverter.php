<?php

namespace Pim\Component\Api\Converter;

use Pim\Component\Connector\ArrayConverter\ArrayConverterInterface;

/**
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MeasureFamilyConverter implements ArrayConverterInterface
{
    /**
     * {@inheritdoc}
     */
    public function convert(array $item, array $options = [])
    {
        $convertedItem = [
            "code" => $item['family_code'],
            "standard" => $item['units']['standard'],
            "units" => $this->convertUnits($item['units']),
        ];

        return $convertedItem;
    }

    /**
     * @param array $units
     *
     * @return array
     */
    protected function convertUnits(array $units)
    {
        $convertedUnits = [];
        foreach ($units['units'] as $code => $unit) {
            $convertedUnits[] = [
                "code" => $code,
                "convert" => call_user_func_array('array_merge', $unit['convert']),
                "symbol" => $unit['symbol'],
            ];
        }

        return $convertedUnits;
    }
}
