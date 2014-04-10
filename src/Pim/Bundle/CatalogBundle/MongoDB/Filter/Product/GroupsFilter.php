<?php

namespace Pim\Bundle\CatalogBundle\MongoDB\Filter\Product;

use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Filter\ChoiceFilter;

/**
 * MongoDB ODM Product groups filter
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupsFilter extends ChoiceFilter
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

        $qb = $ds->getQueryBuilder();
        $ids = $data['value'];
        $repository = $this->util->getProductRepository();
        $pqb = $repository->getProductQueryBuilder($qb);
        $pqb->addFieldFilter('groupIds', 'IN', $ids);

        return true;
    }
}
