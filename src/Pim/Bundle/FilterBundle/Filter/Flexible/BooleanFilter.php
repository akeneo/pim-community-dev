<?php

namespace Pim\Bundle\FilterBundle\Filter\Flexible;

use Oro\Bundle\FilterBundle\Filter\BooleanFilter as OroBooleanFilter;
use Oro\Bundle\FilterBundle\Form\Type\Filter\BooleanFilterType;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;

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
