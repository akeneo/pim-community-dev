<?php

namespace Pim\Bundle\FilterBundle\Filter\Product;

use Symfony\Component\Form\FormFactoryInterface;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Filter\BooleanFilter;
use Oro\Bundle\FilterBundle\Form\Type\Filter\BooleanFilterType;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\FilterBundle\Filter\FilterUtility;

/**
 * Product in group filter (used by group products grid)
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InGroupFilter extends BooleanFilter
{
    /**
     * @var RequestParameters
     */
    protected $requestParams;

    /**
     * Constructor
     *
     * @param FormFactoryInterface $factory
     * @param FilterUtility        $util
     * @param RequestParameters    $requestParams
     */
    public function __construct(
        FormFactoryInterface $factory,
        FilterUtility $util,
        RequestParameters $requestParams
    ) {
        parent::__construct($factory, $util);
        $this->requestParams = $requestParams;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(FilterDatasourceAdapterInterface $ds, $data)
    {
        $data = $this->parseData($data);

        if (!$data) {
            return false;
        }

        $groupId = $this->requestParams->get('currentGroup', null);
        if (!$groupId) {
            throw new \LogicalException('The current product group must be configured');
        }

        $value = $groupId;
        $operator = ($data['value'] === BooleanFilterType::TYPE_YES) ? 'IN' : 'NOT IN';

        $qb = $ds->getQueryBuilder();
        $repository = $this->util->getProductRepository();

        $repository->applyFilterByField($qb, 'groups', $value, $operator);

        return true;
    }
}
