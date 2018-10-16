<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Component\Publisher;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Publisher\PublisherInterface;
use PhpSpec\ObjectBehavior;

class DateTimePublisherSpec extends ObjectBehavior
{
    function it_is_a_publisher()
    {
        $this->shouldBeAnInstanceOf(PublisherInterface::class);
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
