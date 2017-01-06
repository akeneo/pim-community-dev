<?php

namespace spec\PimEnterprise\Component\ActivityManager\Model;

use PimEnterprise\Component\ActivityManager\Model\AttributeGroupCompleteness;
use PhpSpec\ObjectBehavior;

class AttributeGroupCompletenessSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(40, 0, 1);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AttributeGroupCompleteness::class);
    }

    function it_has_an_attribute_group_id()
    {
        $this->getAttributeGroupId()->shouldReturn(40);
    }

    function it_has_at_least_one_attribute_filled()
    {
        $this->hasAtLeastOneAttributeFilled()->shouldReturn(0);
    }

    function it_is_complete()
    {
        $this->isComplete()->shouldReturn(1);
    }
}
