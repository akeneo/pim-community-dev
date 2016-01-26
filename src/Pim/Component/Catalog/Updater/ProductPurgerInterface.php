<?php

namespace Pim\Component\Catalog\Updater;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;

/**
 * Remove empty product values from a product
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ProductPurgerInterface
{
    /**
     * @param ProductInterface $product
     *
     * @return bool has removed values
     */
    public function removeEmptyProductValues(ProductInterface $product);
}
