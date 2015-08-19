<?php

namespace spec\Pim\Component\Catalog\Completeness\Checker;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Component\Catalog\Completeness\Checker\ProductValueCompleteCheckerInterface;

class ChainedProductValueCompleteCheckerSpec extends ObjectBehavior
{
    public function it_is_a_completeness_checker()
    {
        $this->shouldImplement('Pim\Component\Catalog\Completeness\Checker\ProductValueCompleteCheckerInterface');
    }

    public function it_succesfully_checks_incomplete_attribute(
        ProductValueInterface $value,
        ChannelInterface $channel,
        LocaleInterface $locale,
        ProductValueCompleteCheckerInterface $completenessChecker,
        AttributeInterface $attribute
    ) {
        $completenessChecker->supportsValue($value)->willReturn(true);
        $completenessChecker->isComplete($value, $channel, $locale)->willReturn(false);

        $this->addProductValueChecker($completenessChecker);

        $value->getAttribute()->willReturn($attribute);
        $value->getData()->willReturn('foo');

        $this->isComplete($value, $channel, $locale)->shouldReturn(false);
    }

    public function it_succesfully_checks_simple_complete_attribute(
        ProductValueInterface $value,
        ChannelInterface $channel,
        LocaleInterface $locale
    ) {
        $value->getData()->willReturn('foo');
        $this->isComplete($value, $channel, $locale)->shouldReturn(true);

        $value->getData()->willReturn(['foo', 'bar']);
        $this->isComplete($value, $channel, $locale)->shouldReturn(true);
    }

    public function it_succesfully_checks_complete_attribute(
        ProductValueInterface $value,
        ChannelInterface $channel,
        LocaleInterface $locale,
        ProductValueCompleteCheckerInterface $completenessChecker,
        AttributeInterface $attribute
    ) {
        $value->getAttribute()->willReturn($attribute);
        $value->getData()->willReturn('foo');

        $this->addProductValueChecker($completenessChecker);

        $completenessChecker->supportsValue($value)->willReturn(true);
        $completenessChecker->isComplete($value, $channel, $locale)->willReturn(true);

        $this->isComplete($value, $channel, $locale)->shouldReturn(true);
    }
}
