<?php

namespace Oro\Bundle\FlexibleEntityBundle\Grid\Extension\Filter;

use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\FilterBundle\Filter\Orm\NumberFilter;

class FlexibleNumberFilter extends NumberFilter
{
    /**
     * {@inheritdoc}
     */
    public function apply(QueryBuilder $qb, $data)
    {
        $data = $this->parseData($data);
        if (!$data) {
            return false;
        }

        $operator = $this->getOperator($data['type']);

        $fen = $this->get(FilterUtility::FEN_KEY);
        $this->util->applyFlexibleFilter(
            $qb,
            $fen,
            $this->get(FilterUtility::DATA_NAME_KEY),
            $data['value'],
            $operator
        );

        return true;
    }
}
