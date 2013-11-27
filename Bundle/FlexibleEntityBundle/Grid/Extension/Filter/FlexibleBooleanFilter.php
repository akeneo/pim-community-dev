<?php

namespace Oro\Bundle\FlexibleEntityBundle\Grid\Extension\Filter;

use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\FilterBundle\Filter\Orm\BooleanFilter;
use Oro\Bundle\FilterBundle\Form\Type\Filter\BooleanFilterType;

class FlexibleBooleanFilter extends BooleanFilter
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

        $field = $this->get(FilterUtility::DATA_NAME_KEY);
        $value = ($data['value'] == BooleanFilterType::TYPE_YES) ? 1 : 0;

        $this->util->applyFlexibleFilter($qb, $this->get(FilterUtility::FEN_KEY), $field, $value, '=');

        return true;
    }
}
