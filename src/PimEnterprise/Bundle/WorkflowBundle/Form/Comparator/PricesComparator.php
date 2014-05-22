<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Form\Comparator;

use Pim\Bundle\CatalogBundle\Model\AbstractProductValue;

/**
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class PricesComparator implements ComparatorInterface
{
    public function supportsComparison(AbstractProductValue $value)
    {
        return 'pim_catalog_price_collection' === $value->getAttribute()->getAttributeType();
    }

    public function getChanges(AbstractProductValue $value, $submittedData)
    {
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

        return !empty($changes) ? $changes : null;
    }
}
