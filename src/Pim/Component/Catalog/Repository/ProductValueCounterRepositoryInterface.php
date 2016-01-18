<?php

namespace Pim\Component\Catalog\Repository;

/**
 * Product value repository used to retrieve the number of product values. This number can be used
 * to know whether MongoDB support should be enabled or not.
 *
 * @author    Remy Betus <remy.betus@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ProductValueCounterRepositoryInterface
{
    /**
     * Counts the number of product values.
     *
     * @return int
     */
    public function count();
}
