<?php

namespace Oro\Bundle\PimDataGridBundle\Extension\Sorter\Product;

use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\PimDataGridBundle\Extension\Sorter\SorterInterface;

/**
 * Product in group sorter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InGroupSorter implements SorterInterface
{
    /**
     * @var RequestParameters
     */
    protected $requestParams;

    /**
     * @param RequestParameters $requestParams
     */
    public function __construct(RequestParameters $requestParams)
    {
        $this->requestParams = $requestParams;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(DatasourceInterface $datasource, $field, $direction)
    {
        $groupId = $this->requestParams->get('currentGroup', null);
        if (!$groupId) {
            throw new \LogicException('The current product group must be configured');
        }

        $field = 'in_group_'.$groupId;
        $datasource->getProductQueryBuilder()->addSorter($field, $direction);
    }
}
