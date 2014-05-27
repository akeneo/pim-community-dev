<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Factory;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProduct;
use PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductValue;

/**
 * Published product factory
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class PublishedProductFactory
{
    /**
     * Create/update a published product instance
     *
     * @param ProductInterface $product
     *
     * @return PublishedProduct
     */
    public function publish(ProductInterface $product)
    {
        $published = new PublishedProduct();
        foreach ($product->getValues() as $originalValue) {
            $publishedValue = new PublishedProductValue();
            $publishedValue->setAttribute($originalValue->getAttribute());
            $publishedValue->setLocale($originalValue->getLocale());
            $publishedValue->setScope($originalValue->getScope());

            $originalData = $originalValue->getData();
            if (is_object($originalData)) {
                // TODO not deal with object for now
                continue;

                $copiedData = clone $originalData;
            } else {
                $copiedData = $originalData;
            }
            $publishedValue->setData($copiedData);

            $published->addValue($publishedValue);
        }


/*        $proposal
            ->setProduct($product)
            ->setAuthor($username)
            ->setCreatedAt(new \DateTime())
            ->setChanges($changes);
*/

        return $published;
    }
}
