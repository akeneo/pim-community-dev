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

use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Filter\FilterUtility;
use Pim\Bundle\FilterBundle\Filter\AjaxChoiceFilter;

/**
 * Tag filter
 *
 * @author JM Leroux <jean-marie@akeneo.com>
 */
class TagFilter extends AjaxChoiceFilter
{
    /** @var TagFilterAwareInterface */
    protected $util;

    /**
     * {@inheritdoc}
     */
    public function apply(FilterDatasourceAdapterInterface $dataSource, $data)
    {
        $filterColumn = $this->get(FilterUtility::DATA_NAME_KEY);
        $operator     = $this->getOperator($data['type']);

        $this->util->applyTagFilter($dataSource, $filterColumn, $operator, $data['value']);

        return true;
    }
}
