<?php

namespace Pim\Component\Catalog\Comparator\Attribute;

use Pim\Component\Catalog\Comparator\ComparatorInterface;

/**
 * Comparator which calculate change set for prices
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PricesComparator implements ComparatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($type)
    {
        return 'pim_catalog_price_collection' === $type;
    }

    /**
     * {@inheritdoc}
     */
    public function compare($data, $originals)
    {
        $default = ['locale' => null, 'scope' => null, 'value' => []];
        $originals = array_merge($default, $originals);

        $originalPrices = [];
        foreach ($originals['value'] as $price) {
            $originalPrices[$price['currency']] = $price['data'];
        }

        $dataPrices = [];
        foreach ($data['value'] as $price) {
            $dataPrices[$price['currency']] = $price['data'];
        }

        if ($dataPrices !== $originalPrices) {
            return $data;
        }

        return null;
    }
}
