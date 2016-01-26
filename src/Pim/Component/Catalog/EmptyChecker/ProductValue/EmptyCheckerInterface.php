<?php

namespace Pim\Component\Catalog\EmptyChecker\ProductValue;

use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;

/**
 * Empty product value checker, determines whether a product value is empty depending on its attribute type
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface EmptyCheckerInterface
{
    /**
     * Check if the product value is empty
     *
     * @param ProductValueInterface $productValue
     *
     * @return bool the product value is empty
     */
    public function isEmpty(ProductValueInterface $productValue);

    /**
     * Supports the product value
     *
     * @param ProductValueInterface $productValue
     *
     * @return bool
     */
    public function supports(ProductValueInterface $productValue);
}
