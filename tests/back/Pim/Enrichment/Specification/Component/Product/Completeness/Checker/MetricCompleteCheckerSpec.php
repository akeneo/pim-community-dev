<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Completeness\Checker;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\Checker\ValueCompleteCheckerInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\MetricInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;

class MetricCompleteCheckerSpec extends ObjectBehavior
{
    function let(IdentifiableObjectRepositoryInterface $attributeRepository)
    {
        $this->beConstructedWith($attributeRepository);
    }

    public function it_is_a_completeness_checker()
    {
        $this->shouldImplement(ValueCompleteCheckerInterface::class);
    }

    public function it_suports_metric_attribute(
        ValueInterface $value,
        AttributeInterface $attribute,
        ChannelInterface $channel,
        LocaleInterface $locale,
        $attributeRepository
    ) {
        $attributeRepository->findOneByIdentifier('my_metric')->willReturn($attribute);
        $value->getAttributeCode()->willReturn('my_metric');
        $attribute->getType()->willReturn(AttributeTypes::METRIC);
        $this->supportsValue($value, $channel, $locale)->shouldReturn(true);

        $attribute->getType()->willReturn('other');
        $this->supportsValue($value, $channel, $locale)->shouldReturn(false);
    }

    public function it_successfully_checks_complete_metric(
        ValueInterface $value,
        MetricInterface $metric,
        ChannelInterface $channel,
        LocaleInterface $locale
    ) {
        $value->getData()->willReturn($metric);

        $metric->getData()->willReturn(200);
        $metric->getBaseData()->willReturn(2);
        $metric->getUnit()->willReturn('CENTIMETER');
        $metric->getBaseUnit()->willReturn('METER');
        $this->isComplete($value, $channel, $locale)->shouldReturn(true);

        $metric->getData()->willReturn(0);
        $metric->getBaseData()->willReturn(0);
        $metric->getUnit()->willReturn('GRAM');
        $metric->getBaseUnit()->willReturn('KILOGRAM');
        $this->isComplete($value, $channel, $locale)->shouldReturn(true);
    }

    public function it_checks_empty_value(
        ValueInterface $value,
        ChannelInterface $channel,
        LocaleInterface $locale
    ) {
        $value->getData()->willReturn(null);
        $this->isComplete($value, $channel, $locale)->shouldReturn(false);
    }

    public function it_checks_incomplete_metric(
        ValueInterface $value,
        MetricInterface $metric,
        ChannelInterface $channel,
        LocaleInterface $locale
    ) {
        $value->getData()->willReturn($metric);

        $metric->getData()->willReturn(null);
        $metric->getBaseData()->willReturn(2);
        $metric->getUnit()->willReturn('CENTIMETER');
        $metric->getBaseUnit()->willReturn('METER');
        $this->isComplete($value, $channel, $locale)->shouldReturn(false);

        $metric->getData()->willReturn(200);
        $metric->getBaseData()->willReturn(null);
        $metric->getUnit()->willReturn('CENTIMETER');
        $metric->getBaseUnit()->willReturn('METER');
        $this->isComplete($value, $channel, $locale)->shouldReturn(false);

        $metric->getData()->willReturn(200);
        $metric->getBaseData()->willReturn(2);
        $metric->getUnit()->willReturn(null);
        $metric->getBaseUnit()->willReturn('METER');
        $this->isComplete($value, $channel, $locale)->shouldReturn(false);

        $metric->getData()->willReturn(200);
        $metric->getBaseData()->willReturn(2);
        $metric->getUnit()->willReturn('CENTIMETER');
        $metric->getBaseUnit()->willReturn(null);
        $this->isComplete($value, $channel, $locale)->shouldReturn(false);

        $metric->getData()->willReturn('');
        $metric->getBaseData()->willReturn(2);
        $metric->getUnit()->willReturn('CENTIMETER');
        $metric->getBaseUnit()->willReturn('METER');
        $this->isComplete($value, $channel, $locale)->shouldReturn(false);

        $metric->getData()->willReturn(200);
        $metric->getBaseData()->willReturn('');
        $metric->getUnit()->willReturn('CENTIMETER');
        $metric->getBaseUnit()->willReturn('METER');
        $this->isComplete($value, $channel, $locale)->shouldReturn(false);

        $metric->getData()->willReturn(200);
        $metric->getBaseData()->willReturn(2);
        $metric->getUnit()->willReturn('');
        $metric->getBaseUnit()->willReturn('METER');
        $this->isComplete($value, $channel, $locale)->shouldReturn(false);

        $metric->getData()->willReturn(200);
        $metric->getBaseData()->willReturn(2);
        $metric->getUnit()->willReturn('CENTIMETER');
        $metric->getBaseUnit()->willReturn('');
        $this->isComplete($value, $channel, $locale)->shouldReturn(false);
    }
}
