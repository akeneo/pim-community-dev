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

use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;

/**
 * Comparator which calculate change set for prices
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 *
 * @see    PimEnterprise\Bundle\WorkflowBundle\Form\ComparatorInterface
 */
class PricesComparator implements ComparatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportsComparison(ProductValueInterface $value)
    {
        return 'pim_catalog_price_collection' === $value->getAttribute()->getAttributeType();
    }

    /**
     * {@inheritdoc}
     */
    public function getChanges(ProductValueInterface $value, $submittedData)
    {
        if (!isset($submittedData['prices'])) {
            return;
        }

        $changes = [];
        $currentPrices = $value->getPrices();
        foreach ($submittedData['prices'] as $currency => $price) {
            if (null === $priceObject = $currentPrices[$currency]) {
                continue;
            }
            if ($priceObject->getData() != $price['data']) {
                $changes['prices'][$currency] = $price;
            }
        }

        if (!empty($changes)) {
            return $changes;
        }
    }
}
