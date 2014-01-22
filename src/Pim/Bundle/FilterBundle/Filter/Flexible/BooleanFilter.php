<?php

namespace Pim\Bundle\FilterBundle\Filter\Flexible;

use Oro\Bundle\FilterBundle\Filter\BooleanFilter as OroBooleanFilter;
use Oro\Bundle\FilterBundle\Form\Type\Filter\BooleanFilterType;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;

/**
 * Flexible filter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BooleanFilter extends OroBooleanFilter
{
    /**
     * {@inheritdoc}
     */
    public function apply(FilterDatasourceAdapterInterface $ds, $data)
    {
        if (!$data = $this->parseData($data)) {
            return false;
        }

        $this->util->applyFlexibleFilter(
            $ds,
            $this->get(FilterUtility::FEN_KEY),
            $this->get(FilterUtility::DATA_NAME_KEY),
            $data['value'],
            '='
        );

        return true;
    }
}
