<?php

namespace Oro\Bundle\GridBundle\Filter\ORM;

use Oro\Bundle\FilterBundle\Form\Type\Filter\DateRangeFilterType;

class DateRangeFilter extends AbstractDateFilter
{
    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions()
    {
        return array(
            'form_type' => DateRangeFilterType::NAME
        );
    }
}
