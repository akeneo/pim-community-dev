<?php

namespace Pim\Component\Catalog\Comparator\Filter;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;

/**
 * Product filter interface
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ProductFilterInterface
{
    /**
     * Filter product's values to have only updated or new values
     *
     * @param ProductInterface $product
     * @param array            $newValues
     *
     * @return array
     */
    public function filter(ProductInterface $product, array $newValues);
}
