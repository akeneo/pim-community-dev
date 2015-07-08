<?php

namespace spec\Pim\Bundle\CatalogBundle\Completeness\Checker;

use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Completeness\Checker\Attribute\AttributeCompleteCheckerInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;

class ProductValueCompleteCheckerSpec extends ObjectBehavior
{
    public function it_succesfully_checks_incomplete_attribute(
        ProductValueInterface $value,
        ChannelInterface $channel,
        AttributeCompleteCheckerInterface $attributeCompleteChecker,
        AttributeInterface $attribute
    ) {
        $value->getData()->willReturn(null);
        $this->isComplete($value, $channel, 'en_US')->shouldReturn(false);

        $value->getData()->willReturn('');
        $this->isComplete($value, $channel, 'en_US')->shouldReturn(false);

        $value->getData()->willReturn([]);
        $this->isComplete($value, $channel, 'en_US')->shouldReturn(false);

        $value->getData()->willReturn(new ArrayCollection());
        $this->isComplete($value, $channel, 'en_US')->shouldReturn(false);

        $value->getAttribute()->willReturn($attribute);
        $value->getData()->willReturn('foo');

        $attributeCompleteChecker->supportsAttribute($attribute)->willReturn(true);
        $attributeCompleteChecker->isComplete($value, $channel, 'en_US')->willReturn(false);
        $this->addAttributeChecker($attributeCompleteChecker);
        $this->isComplete($value, $channel, 'en_US')->shouldReturn(false);
    }

    public function it_succesfully_checks_complete_attribute(
        ProductValueInterface $value,
        ChannelInterface $channel,
        AttributeCompleteCheckerInterface $attributeCompleteChecker,
        AttributeInterface $attribute
    ) {
        $value->getData()->willReturn('foo');
        $this->isComplete($value, $channel, 'en_US')->shouldReturn(true);

        $value->getData()->willReturn(['foo', 'bar']);
        $this->isComplete($value, $channel, 'en_US')->shouldReturn(true);

        $value->getAttribute()->willReturn($attribute);
        $value->getData()->willReturn('foo');

        $attributeCompleteChecker->supportsAttribute($attribute)->willReturn(true);
        $attributeCompleteChecker->isComplete($value, $channel, 'en_US')->willReturn(true);
        $this->addAttributeChecker($attributeCompleteChecker);
        $this->isComplete($value, $channel, 'en_US')->shouldReturn(true);

        $attributeCompleteChecker->supportsAttribute($attribute)->willReturn(false);
        $attributeCompleteChecker->isComplete($value, $channel, 'en_US')->willReturn(false);
        $this->addAttributeChecker($attributeCompleteChecker);
        $this->isComplete($value, $channel, 'en_US')->shouldReturn(true);
    }
}
