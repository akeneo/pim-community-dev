<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace PimEnterprise\Bundle\FilterBundle\Filter\Tag;

use Pim\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;

/**
 * Tag filter
 *
 * @author JM Leroux <jean-marie@akeneo.com>
 */
interface TagFilterAwareInterface
{
    /**
     * Apply tag filter
     *
     * @param FilterDatasourceAdapterInterface $ds
     * @param string                           $field
     * @param string                           $operator
     * @param mixed                            $value
     */
    public function applyTagFilter(FilterDatasourceAdapterInterface $ds, $field, $operator, $value);
}
