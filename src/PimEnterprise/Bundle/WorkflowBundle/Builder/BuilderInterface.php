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

use Doctrine\Common\Collections\Collection;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;

/**
 * Builder to compare values
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
interface BuilderInterface
{
    /**
     * @param ProductInterface $product
     */
    public function builder(ProductInterface $product);
}
