<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Comparator;

/**
 * Comparator which calculate change set for prices
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 */
class PricesComparator implements ComparatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportsComparison($type)
    {
        return 'pim_catalog_price_collection' === $type;
    }

    /**
     * {@inheritdoc}
     */
    public function getChanges(array $data, array $originals)
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
