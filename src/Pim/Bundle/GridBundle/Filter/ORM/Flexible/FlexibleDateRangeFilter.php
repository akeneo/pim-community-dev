<?php

namespace Pim\Bundle\GridBundle\Filter\ORM\Flexible;

class FlexibleDateRangeFilter extends AbstractFlexibleDateFilter
{
    /**
     * @var string
     */
    protected $parentFilterClass = 'Oro\\Bundle\\GridBundle\\Filter\\ORM\\DateRangeFilter';
}
