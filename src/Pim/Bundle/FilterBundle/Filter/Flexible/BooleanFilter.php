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
        $data = $this->parseData($data);
        if (!$data) {
            return false;
        }

        $field = $this->get(FilterUtility::DATA_NAME_KEY);
        $value = ($data['value'] == BooleanFilterType::TYPE_YES) ? 1 : 0;

        $this->util->applyFlexibleFilter($ds, $this->get(FilterUtility::FEN_KEY), $field, $value, '=');

        return true;
    }
}
