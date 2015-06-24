<?php

namespace Pim\Component\Catalog\Comparator;

/**
 * Comparator which calculate change set for prices
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
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

        $prices = [];
        foreach ($data['value'] as $price) {
            $currency = $price['currency'];
            if (!array_key_exists($currency, $originalPrices) || $originalPrices[$currency] !== $price['data']) {
                $prices[] = $price;
            }
        }

        if (!empty($prices)) {
            return [
                'locale' => $data['locale'],
                'scope'  => $data['scope'],
                'value'  => $prices
            ];
        }

        return null;
    }
}
