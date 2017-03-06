<?php

namespace spec\PimEnterprise\Bundle\TeamworkAssistantBundle\Datagrid;

use PimEnterprise\Bundle\TeamworkAssistantBundle\Datagrid\DatagridViewTypes;
use PhpSpec\ObjectBehavior;

class DatagridViewTypesSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(DatagridViewTypes::class);
    }
}
