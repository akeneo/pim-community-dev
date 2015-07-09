<?php

namespace spec\PimEnterprise\Bundle\ProductAssetBundle\Connector\Step;

use PhpSpec\ObjectBehavior;

class TagStepSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('aName');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('\PimEnterprise\Bundle\ProductAssetBundle\Connector\Step\TagStep');
    }

    function it_is_a_step()
    {
        $this->shouldHaveType('\Akeneo\Bundle\BatchBundle\Step\StepInterface');
        $this->shouldHaveType('\Akeneo\Bundle\BatchBundle\Step\AbstractStep');
    }
}
