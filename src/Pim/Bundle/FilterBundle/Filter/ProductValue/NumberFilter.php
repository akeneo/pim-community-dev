<?php

namespace Pim\Bundle\FilterBundle\Filter\ProductValue;

use Oro\Bundle\FilterBundle\Filter\NumberFilter as OroNumberFilter;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Pim\Bundle\FilterBundle\Filter\ProductFilterUtility;

/**
 * Number filter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NumberFilter extends OroNumberFilter
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

        $operator = $this->getOperator($data['type']);

        $this->util->applyFlexibleFilter(
            $ds,
            $this->get(ProductFilterUtility::DATA_NAME_KEY),
            $data['value'],
            $operator
        );

        return true;
    }
}
