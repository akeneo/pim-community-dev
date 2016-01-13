<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogBundle\Query\Filter;

use Pim\Bundle\CatalogBundle\Query\Filter\Operators as BaseOperators;

/**
 * Filter operators
 *
 * @author Clement Gautier <clement.gautier@akeneo.com>
 */
class Operators extends BaseOperators
{
    const IN_ARRAY_KEYS = 'IN ARRAY KEYS';
}
