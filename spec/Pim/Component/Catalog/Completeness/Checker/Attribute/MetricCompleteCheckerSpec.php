<?php

namespace spec\Pim\Component\Catalog\Completeness\Checker\Attribute;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Model\MetricInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;

class MetricCompleteCheckerSpec extends ObjectBehavior
{
    public function it_suports_metric_attribute(
        AttributeInterface $attribute
    ) {
        $attribute->getAttributeType()->willReturn('pim_catalog_metric');
        $this->supportsAttribute($attribute)->shouldReturn(true);

        $attribute->getAttributeType()->willReturn('other');
        $this->supportsAttribute($attribute)->shouldReturn(false);
    }

    public function it_succesfully_checks_complete_metric(
        ProductValueInterface $value,
        ChannelInterface $channel,
        MetricInterface $metric
    ) {
        $value->getMetric()->willReturn(null);
        $this->isComplete($value, $channel, 'en_US')->shouldReturn(false);

        $value->getMetric()->willReturn([]);
        $this->isComplete($value, $channel, 'en_US')->shouldReturn(false);

        $metric->getData()->willReturn(null);
        $value->getMetric()->willReturn($metric);
        $this->isComplete($value, $channel, 'en_US')->shouldReturn(false);

        $metric->getData()->willReturn('foobar');
        $value->getMetric()->willReturn($metric);
        $this->isComplete($value, $channel, 'en_US')->shouldReturn(true);
    }
}
