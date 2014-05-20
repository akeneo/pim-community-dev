<?php

namespace Pim\Bundle\CatalogBundle\Persistence;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;

/**
 * Synchronize product with the database
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ProductPersister
{
    /**
     * Save a product in the database
     *
     * @param ProductInterface $product
     * @param array            $options
     */
    public function persist(ProductInterface $product, array $options);
}
