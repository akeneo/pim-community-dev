<?php

namespace PimEnterprise\Bundle\CatalogBundle\Persistence;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;

/**
 * Provide changes that occured on product values
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
interface ProductChangesProvider
{
    /**
     * Compute a product new values
     *
     * @param ProductInterface $product
     *
     * @return array
     */
    public function computeNewValues(ProductInterface $product);
}
