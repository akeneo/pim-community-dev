<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Form\Comparator;

use Pim\Bundle\CatalogBundle\Model\AbstractProductValue;

/**
 * Comparator which calculate change set for prices
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * @see       PimEnterprise\Bundle\WorkflowBundle\Form\ComparatorInterface
 */
class PricesComparator implements ComparatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportsComparison(AbstractProductValue $value)
    {
        return 'pim_catalog_price_collection' === $value->getAttribute()->getAttributeType();
    }

    /**
     * {@inheritdoc}
     */
    public function getChanges(AbstractProductValue $value, $submittedData)
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
