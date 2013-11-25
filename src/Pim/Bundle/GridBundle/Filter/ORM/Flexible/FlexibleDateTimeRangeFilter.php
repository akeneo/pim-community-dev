<?php

namespace Pim\Bundle\GridBundle\Filter\ORM\Flexible;

class FlexibleDateTimeRangeFilter extends AbstractFlexibleDateFilter
{
    /**
     * @var string
     */
    protected $parentFilterClass = 'Oro\\Bundle\\GridBundle\\Filter\\ORM\\DateTimeRangeFilter';
}
