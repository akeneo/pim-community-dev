<?php

namespace spec\PimEnterprise\Bundle\TeamworkAssistantBundle\Datagrid;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\TeamworkAssistantBundle\Datagrid\DatagridViewTypes;

class DatagridViewTypesSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(DatagridViewTypes::class);
    }
}
