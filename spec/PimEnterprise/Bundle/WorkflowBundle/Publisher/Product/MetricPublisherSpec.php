<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Publisher\Product;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AbstractMetric;

class MetricPublisherSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductMetric');
    }

    function it_is_a_publisher()
    {
        $this->shouldBeAnInstanceOf('PimEnterprise\Bundle\WorkflowBundle\Publisher\PublisherInterface');
    }

    function it_supports_metric(AbstractMetric $value)
    {
        $this->supports($value)->shouldBe(true);
    }

    function it_publishes_metric(AbstractMetric $value)
    {
        $this
            ->publish($value)
            ->shouldReturnAnInstanceOf('PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductMetric');
    }
}
