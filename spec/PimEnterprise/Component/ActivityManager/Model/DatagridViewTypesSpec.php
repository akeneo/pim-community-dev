<?php

namespace spec\Akeneo\ActivityManager\Component\Model;

use Akeneo\ActivityManager\Component\Model\DatagridViewTypes;
use PhpSpec\ObjectBehavior;

class DatagridViewTypesSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(DatagridViewTypes::class);
    }
}
