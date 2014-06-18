<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Publisher;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Pim\Bundle\CatalogBundle\Model;

class DateTimePublisherSpec extends ObjectBehavior
{
    function it_is_a_publisher()
    {
        $this->shouldBeAnInstanceOf('PimEnterprise\Bundle\WorkflowBundle\Publisher\PublisherInterface');
    }

    function it_supports_datetime(\DateTime $value) {
        $this->supports($value)->shouldBe(true);
    }

    function it_publishes_datetime(\DateTime $value) {
        $this->publish($value)->shouldReturnAnInstanceOf('DateTime');
    }
}
