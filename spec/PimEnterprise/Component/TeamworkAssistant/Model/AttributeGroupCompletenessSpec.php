<?php

namespace spec\PimEnterprise\Component\TeamworkAssistant\Model;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Component\TeamworkAssistant\Model\AttributeGroupCompleteness;

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

    function it_has_calculated_at()
    {
        $this->getCalculatedAt()->shouldHaveType(\DateTimeInterface::class);
    }
}
