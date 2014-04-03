<?php

namespace Pim\Bundle\CatalogBundle\MongoDB\Filter\Product;

use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Pim\Bundle\FilterBundle\Filter\Product\InGroupFilter as BaseInGroupFilter;

/**
 * MongoDB ODM product in group filter
 *
 * @author    Gildas Quemeneer <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InGroupFilter extends BaseInGroupFilter
{
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

        $repository->applyFilterByField($qb, 'groupIds', $value, $operator);

        return true;
    }
}
