<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Publisher;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;

class AttributeOptionPublisherSpec extends ObjectBehavior
{
    function it_is_a_publisher()
    {
        $this->shouldBeAnInstanceOf('PimEnterprise\Bundle\WorkflowBundle\Publisher\PublisherInterface');
    }

    function it_supports_attribute_option(AttributeOption $value)
    {
        $this->supports($value)->shouldBe(true);
    }

    function it_publishes_attribute_option(AttributeOption $value)
    {
        $this->publish($value)->shouldReturnAnInstanceOf('Pim\Bundle\CatalogBundle\Entity\AttributeOption');
    }
}
