<?php

namespace Oro\Bundle\FilterBundle\Extension\Orm;

use Oro\Bundle\FilterBundle\Form\Type\Filter\DateRangeFilterType;

class DateRangeFilter extends AbstractDateFilter
{
    /**
     * {@inheritdoc}
     */
    protected function getFormType()
    {
        return DateRangeFilterType::NAME;
    }
}
