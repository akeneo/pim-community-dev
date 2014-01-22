<?php

namespace Oro\Bundle\FlexibleEntityBundle\Grid\Extension\Filter;

use Oro\Bundle\FilterBundle\Form\Type\Filter\DateRangeFilterType;

class FlexibleDateRangeFilter extends AbstractFlexibleDateFilter
{
    /**
     * {@inheritdoc}
     */
    protected function getFormType()
    {
        return DateRangeFilterType::NAME;
    }
}
