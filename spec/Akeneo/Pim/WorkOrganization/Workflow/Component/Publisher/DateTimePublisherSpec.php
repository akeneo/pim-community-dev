<?php

namespace spec\Akeneo\Pim\WorkOrganization\Workflow\Component\Publisher;

use PhpSpec\ObjectBehavior;

class DateTimePublisherSpec extends ObjectBehavior
{
    function it_is_a_publisher()
    {
        $this->shouldBeAnInstanceOf('Akeneo\Pim\WorkOrganization\Workflow\Component\Publisher\PublisherInterface');
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
