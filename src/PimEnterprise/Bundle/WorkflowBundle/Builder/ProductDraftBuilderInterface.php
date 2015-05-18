<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Builder;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;

/**
 * Builder to compare values of product
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
interface ProductDraftBuilderInterface
{
    /**
     * @param ProductInterface $product
     *
     * @throws \LogicException
     *
     * @return array
     */
    public function build(ProductInterface $product);
}
