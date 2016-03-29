<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\Workflow\Builder;

use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Component\Workflow\Model\ProductDraftInterface;

/**
 * Product draft builder interface
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
interface ProductDraftBuilderInterface
{
    /**
     * @param ProductInterface $product
     * @param string           $username
     *
     * @throws \LogicException
     *
     * @return ProductDraftInterface|null returns null if no draft is created
     */
    public function build(ProductInterface $product, $username);
}
