<?php

namespace spec\PimEnterprise\Component\Workflow\Publisher\Product;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductPriceInterface;

class PricePublisherSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('PimEnterprise\Component\Workflow\Model\PublishedProductPrice');
    }

    function it_is_a_publisher()
    {
        $this->shouldBeAnInstanceOf('PimEnterprise\Component\Workflow\Publisher\PublisherInterface');
    }

    function it_supports_price(ProductPriceInterface $value)
    {
        $this->supports($value)->shouldBe(true);
    }

    function it_publishes_price(ProductPriceInterface $value)
    {
        $this
            ->publish($value)
            ->shouldReturnAnInstanceOf('PimEnterprise\Component\Workflow\Model\PublishedProductPrice');
    }
}
