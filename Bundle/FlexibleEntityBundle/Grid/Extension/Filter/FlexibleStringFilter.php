<?php

namespace Oro\Bundle\FlexibleEntityBundle\Grid\Extension\Filter;

use Oro\Bundle\FilterBundle\Filter\StringFilter;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;

class FlexibleStringFilter extends StringFilter
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

        $fen = $this->get(FilterUtility::FEN_KEY);
        $this->util->applyFlexibleFilter(
            $ds,
            $fen,
            $this->get(FilterUtility::DATA_NAME_KEY),
            $data['value'],
            $operator
        );

        return true;
    }
}
