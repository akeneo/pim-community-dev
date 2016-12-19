<?php

namespace spec\PimEnterprise\Component\ActivityManager\Model;

use PimEnterprise\Component\ActivityManager\Model\DatagridViewTypes;
use PhpSpec\ObjectBehavior;

class DatagridViewTypesSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(DatagridViewTypes::class);
    }
}
