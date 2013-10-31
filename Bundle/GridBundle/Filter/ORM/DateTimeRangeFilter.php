<?php

namespace Oro\Bundle\GridBundle\Filter\ORM;

use Oro\Bundle\FilterBundle\Form\Type\Filter\DateTimeRangeFilterType;

class DateTimeRangeFilter extends AbstractDateFilter
{
    /**
     * DateTime object as string format
     */
    const DATETIME_FORMAT = 'Y-m-d H:i:s';

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions()
    {
        return array(
            'form_type' => DateTimeRangeFilterType::NAME
        );
    }
}
