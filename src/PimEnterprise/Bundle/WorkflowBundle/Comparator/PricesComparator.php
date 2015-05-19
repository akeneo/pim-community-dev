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
class PricesComparator implements AttributeComparatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportsComparison($attributeType)
    {
        return 'pim_catalog_price_collection' === $attributeType;
    }

    /**
     * {@inheritdoc}
     */
    public function getChanges(array $changes, array $originals)
    {
        $originalPrices = [];
        if (array_key_exists('value', $originals)) {
            foreach ($originals['value'] as $price) {
                $originalPrices[$price['currency']] = $price['data'];
            }
        }

        $prices = [];
        foreach ($changes['value'] as $price) {
            $currency = $price['currency'];
            if (!array_key_exists($currency, $originalPrices) || $originalPrices[$currency] !== $price['data']) {
                $prices[] = $price;
            }
        }

        if (!empty($prices)) {
            return [
                'locale' => $changes['locale'],
                'scope'  => $changes['scope'],
                'value'  => $prices
            ];
        }
    }
}
