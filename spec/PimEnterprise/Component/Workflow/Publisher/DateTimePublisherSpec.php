<?php

namespace spec\PimEnterprise\Component\Workflow\Publisher;

use PhpSpec\ObjectBehavior;

class DateTimePublisherSpec extends ObjectBehavior
{
    function it_is_a_publisher()
    {
        $this->shouldBeAnInstanceOf('PimEnterprise\Component\Workflow\Publisher\PublisherInterface');
    }

    function it_supports_datetime(\DateTime $value)
    {
        $this->supports($value)->shouldBe(true);
    }

    function it_publishes_datetime(\DateTime $value)
    {
        $this->publish($value)->shouldReturnAnInstanceOf('DateTime');
    }
}
