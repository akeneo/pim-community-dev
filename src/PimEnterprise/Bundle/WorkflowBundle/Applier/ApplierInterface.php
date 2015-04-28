<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Applier;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;

/**
 * Applier to compare values
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
interface ApplierInterface
{
    /**
     * @param ProductInterface $product
     */
    public function applier(ProductInterface $product);

    /**
     * @param ProductInteface $product
     *
     * @return ApplierInterface
     */
    public function saveOriginalValues(ProductInterface $product);
}
