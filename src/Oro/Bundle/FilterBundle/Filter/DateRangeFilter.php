<?php

namespace Oro\Bundle\FilterBundle\Filter;

use Oro\Bundle\FilterBundle\Form\Type\Filter\DateRangeFilterType;

class DateRangeFilter extends AbstractDateFilter
{
    /**
     * {@inheritdoc}
     */
    protected function getFormType()
    {
        return DateRangeFilterType::class;
    }
}
