<?php

namespace spec\Pim\Component\Catalog\Completeness\Checker;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Completeness\Checker\ProductValueCompleteCheckerInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;

class TextCollectionCompleteCheckerSpec extends ObjectBehavior
{
    public function it_is_a_completeness_checker()
    {
        $this->shouldImplement(ProductValueCompleteCheckerInterface::class);
    }

    public function it_suports_text_collection_attribute(
        ProductValueInterface $productValue,
        AttributeInterface $attribute
    ) {
        $productValue->getAttribute()->willReturn($attribute);
        $attribute->getAttributeType()->willReturn(AttributeTypes::TEXT_COLLECTION);
        $this->supportsValue($productValue)->shouldReturn(true);

        $attribute->getAttributeType()->willReturn('other');
        $this->supportsValue($productValue)->shouldReturn(false);
    }

    public function it_succesfully_checks_complete_text_collection(
        ProductValueInterface $value,
        ChannelInterface $channel,
        LocaleInterface $locale
    ) {
        $value->getData()->willReturn(['foo']);
        $this->isComplete($value, $channel, $locale)->shouldReturn(true);

        $value->getData()->willReturn(['foo', 'bar']);
        $this->isComplete($value, $channel, $locale)->shouldReturn(true);
    }

    public function it_succesfully_checks_incomplete_text_collection(
        ProductValueInterface $value,
        ChannelInterface $channel,
        LocaleInterface $locale
    ) {
        $value->getData()->willReturn([]);
        $this->isComplete($value, $channel, $locale)->shouldReturn(false);
    }
}
