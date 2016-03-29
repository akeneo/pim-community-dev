<?php

namespace spec\PimEnterprise\Component\Workflow\Publisher\Product;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\MetricInterface;

class MetricPublisherSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('PimEnterprise\Component\Workflow\Model\PublishedProductMetric');
    }

    function it_is_a_publisher()
    {
        $this->shouldBeAnInstanceOf('PimEnterprise\Component\Workflow\Publisher\PublisherInterface');
    }

    function it_supports_metric(MetricInterface $value)
    {
        $this->supports($value)->shouldBe(true);
    }

    function it_publishes_metric(MetricInterface $value)
    {
        $this
            ->publish($value)
            ->shouldReturnAnInstanceOf('PimEnterprise\Component\Workflow\Model\PublishedProductMetric');
    }
}
