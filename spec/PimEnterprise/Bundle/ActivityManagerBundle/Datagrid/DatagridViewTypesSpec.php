<?php

namespace spec\PimEnterprise\Bundle\ActivityManagerBundle\Datagrid;

use PimEnterprise\Bundle\ActivityManagerBundle\Datagrid\DatagridViewTypes;
use PhpSpec\ObjectBehavior;

class DatagridViewTypesSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(DatagridViewTypes::class);
    }
}
