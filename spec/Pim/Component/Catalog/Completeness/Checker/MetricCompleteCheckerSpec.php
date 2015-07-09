<?php

namespace spec\Pim\Component\Catalog\Completeness\Checker;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;
use Pim\Bundle\CatalogBundle\Model\MetricInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;

class MetricCompleteCheckerSpec extends ObjectBehavior
{
    public function it_is_a_completeness_checker()
    {
        $this->shouldImplement('Pim\Component\Catalog\Completeness\Checker\ProductValueCompleteCheckerInterface');
    }

    public function it_suports_metric_attribute(
        ProductValueInterface $productValue,
        AttributeInterface $attribute
    ) {
        $productValue->getAttribute()->willReturn($attribute);
        $attribute->getAttributeType()->willReturn('pim_catalog_metric');
        $this->supportsValue($productValue)->shouldReturn(true);

        $attribute->getAttributeType()->willReturn('other');
        $this->supportsValue($productValue)->shouldReturn(false);
    }

    public function it_succesfully_checks_complete_metric(
        ProductValueInterface $value,
        ChannelInterface $channel,
        LocaleInterface $locale,
        MetricInterface $metric
    ) {
        $value->getMetric()->willReturn(null);
        $this->isComplete($value, $channel, $locale)->shouldReturn(false);

        $value->getMetric()->willReturn([]);
        $this->isComplete($value, $channel, $locale)->shouldReturn(false);

        $metric->getData()->willReturn(null);
        $value->getMetric()->willReturn($metric);
        $this->isComplete($value, $channel, $locale)->shouldReturn(false);

        $metric->getData()->willReturn('foobar');
        $value->getMetric()->willReturn($metric);
        $this->isComplete($value, $channel, $locale)->shouldReturn(true);
    }
}
