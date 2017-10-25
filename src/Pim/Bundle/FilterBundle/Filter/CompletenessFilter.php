<?php

namespace Pim\Bundle\FilterBundle\Filter;

use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Filter\BooleanFilter;
use Oro\Bundle\FilterBundle\Form\Type\Filter\BooleanFilterType;
use Pim\Component\Catalog\Query\Filter\Operators;

/**
 * Overriding of boolean filter to filter by the product completeness
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessFilter extends BooleanFilter
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

        switch ($data['value']) {
            case BooleanFilterType::TYPE_YES:
                $this->util->applyFilter($ds, 'completeness', Operators::AT_LEAST_COMPLETE, null);
                break;
            case BooleanFilterType::TYPE_NO:
                $this->util->applyFilter($ds, 'completeness', Operators::AT_LEAST_INCOMPLETE, null);
                break;
        }


        return true;
    }
}
