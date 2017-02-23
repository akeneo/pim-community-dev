<?php

namespace spec\PimEnterprise\Bundle\TeamWorkAssistantBundle\Datagrid;

use PimEnterprise\Bundle\TeamWorkAssistantBundle\Datagrid\DatagridViewTypes;
use PhpSpec\ObjectBehavior;

class DatagridViewTypesSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(DatagridViewTypes::class);
    }
}
