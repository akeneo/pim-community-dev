<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Publisher;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Pim\Bundle\CatalogBundle\Model;

class ProductPricePublisherSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductPrice');
    }

    function it_is_a_publisher()
    {
        $this->shouldBeAnInstanceOf('PimEnterprise\Bundle\WorkflowBundle\Publisher\PublisherInterface');
    }

    function it_supports_price(Model\AbstractProductPrice $value) {
        $this->supports($value)->shouldBe(true);
    }

    function it_publishes_price(Model\AbstractProductPrice $value) {
        $this->publish($value)->shouldReturnAnInstanceOf('PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductPrice');
    }
}
