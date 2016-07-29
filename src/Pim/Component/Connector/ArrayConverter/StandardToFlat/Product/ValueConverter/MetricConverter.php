<?php

namespace Pim\Component\Connector\ArrayConverter\StandardToFlat\Product\ValueConverter;

/**
 * Metric array converter.
 * Convert a standard metric array format to a flat one.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class MetricConverter extends AbstractValueConverter implements ValueConverterInterface
{
    /**
     * {@inheritdoc}
     *
     * Convert a standard formatted metric product value to a flat one.
     *
     * Given a 'weight' $attributeCode with this $data:
     * [
     *     [
     *         'locale' => 'de_DE',
     *         'scope'  => 'print',
     *         'data'   => [
     *             'unit' => 'MEGAHERTZ',
     *             'data' => '100'
     *         ]
     *     ]
     * ]
     *
     * It will return:
     * [
     *     'weight-de_DE-print'      => '100',
     *     'weight-de_DE-print-unit' => 'MEGAHERTZ',
     * ]
     */
    public function convert($attributeCode, $data)
    {
        $convertedItem = [];

        foreach ($data as $value) {
            $flatName = $this->columnsResolver->resolveFlatAttributeName(
                $attributeCode,
                $value['locale'],
                $value['scope']
            );
            $flatUnitName = sprintf('%s-unit', $flatName);

            $convertedItem[$flatName] = (string) $value['data']['data'];
            $convertedItem[$flatUnitName] = $value['data']['unit'];
        }

        return $convertedItem;
    }
}
