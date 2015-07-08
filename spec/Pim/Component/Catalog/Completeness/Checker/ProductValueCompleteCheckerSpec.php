<?php

namespace spec\Pim\Component\Catalog\Completeness\Checker;

use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Completeness\Checker\Attribute\AttributeCompleteCheckerInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Component\Catalog\Completeness\Checker\CompleteCheckerRegistryInterface;

class ProductValueCompleteCheckerSpec extends ObjectBehavior
{
    public function let(CompleteCheckerRegistryInterface $attributeCompleteCheckerRegistry)
    {
        $this->beConstructedWith($attributeCompleteCheckerRegistry);
    }

    public function it_succesfully_checks_simple_attribute(
        ProductValueInterface $value,
        ChannelInterface $channel
    ) {
        $value->getData()->willReturn(null);
        $this->isComplete($value, $channel, 'en_US')->shouldReturn(false);

        $value->getData()->willReturn('');
        $this->isComplete($value, $channel, 'en_US')->shouldReturn(false);

        $value->getData()->willReturn([]);
        $this->isComplete($value, $channel, 'en_US')->shouldReturn(false);

        $value->getData()->willReturn(new ArrayCollection());
        $this->isComplete($value, $channel, 'en_US')->shouldReturn(false);
    }

    public function it_succesfully_checks_incomplete_attribute(
        ProductValueInterface $value,
        ChannelInterface $channel,
        AttributeCompleteCheckerInterface $attributeCompleteChecker,
        AttributeInterface $attribute,
        $attributeCompleteCheckerRegistry
    ) {
        $attributeCompleteChecker->supportsAttribute($attribute)->willReturn(true);
        $attributeCompleteChecker->isComplete($value, $channel, 'en_US')->willReturn(false);
        $attributeCompleteCheckerRegistry->getAttributeCheckers()->willReturn([$attributeCompleteChecker]);

        $value->getAttribute()->willReturn($attribute);
        $value->getData()->willReturn('foo');

        $this->isComplete($value, $channel, 'en_US')->shouldReturn(false);
    }

    public function it_succesfully_checks_simple_complete_attribute(
        ProductValueInterface $value,
        ChannelInterface $channel,
        $attributeCompleteCheckerRegistry
    ) {
        $attributeCompleteCheckerRegistry->getAttributeCheckers()->willReturn([]);

        $value->getData()->willReturn('foo');
        $this->isComplete($value, $channel, 'en_US')->shouldReturn(true);

        $value->getData()->willReturn(['foo', 'bar']);
        $this->isComplete($value, $channel, 'en_US')->shouldReturn(true);
    }

    public function it_succesfully_checks_complete_attribute(
        ProductValueInterface $value,
        ChannelInterface $channel,
        AttributeCompleteCheckerInterface $attributeCompleteChecker,
        AttributeInterface $attribute,
        $attributeCompleteCheckerRegistry
    ) {
        $value->getAttribute()->willReturn($attribute);
        $value->getData()->willReturn('foo');

        $attributeCompleteChecker->supportsAttribute($attribute)->willReturn(true);
        $attributeCompleteChecker->isComplete($value, $channel, 'en_US')->willReturn(true);
        $attributeCompleteCheckerRegistry->getAttributeCheckers()->willReturn([$attributeCompleteChecker]);
        $this->isComplete($value, $channel, 'en_US')->shouldReturn(true);

        $attributeCompleteChecker->supportsAttribute($attribute)->willReturn(false);
        $attributeCompleteChecker->isComplete($value, $channel, 'en_US')->willReturn(false);
        $attributeCompleteCheckerRegistry->getAttributeCheckers()->willReturn([$attributeCompleteChecker]);
        $this->isComplete($value, $channel, 'en_US')->shouldReturn(true);
    }
}
