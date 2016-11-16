<?php

namespace spec\Akeneo\ActivityManager\Component\Model;

use Akeneo\ActivityManager\Component\Model\CompletenessPerAttributeGroup;
use Akeneo\ActivityManager\Component\Model\CompletenessPerAttributeGroupInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeGroupInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ProductInterface;

class CompletenessPerAttributeGroupSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(CompletenessPerAttributeGroup::class);
    }

    function it_is_a_completeness()
    {
        $this->shouldImplement(CompletenessPerAttributeGroupInterface::class);
    }

    function it_has_a_locale(LocaleInterface $locale)
    {
        $this->setLocale($locale)->shouldReturn(null);
        $this->getLocale()->shouldReturn($locale);
    }

    function it_has_a_channel(ChannelInterface $channel)
    {
        $this->setChannel($channel)->shouldReturn(null);
        $this->getChannel()->shouldReturn($channel);
    }

    function it_has_a_product(ProductInterface $product)
    {
        $this->setProduct($product)->shouldReturn(null);
        $this->getProduct()->shouldReturn($product);
    }

    function it_has_an_attribute_group(AttributeGroupInterface $attributeGroup)
    {
        $this->setAttributeGroup($attributeGroup)->shouldReturn(null);
        $this->getAttributeGroup()->shouldReturn($attributeGroup);
    }
}
