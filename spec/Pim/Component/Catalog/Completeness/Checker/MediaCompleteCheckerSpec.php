<?php

namespace spec\Pim\Component\Catalog\Completeness\Checker;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;
use Pim\Bundle\CatalogBundle\Model\ProductMediaInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;

class MediaCompleteCheckerSpec extends ObjectBehavior
{
    public function it_is_a_completeness_checker()
    {
        $this->shouldImplement('Pim\Component\Catalog\Completeness\Checker\ProductValueCompleteCheckerInterface');
    }

    public function it_suports_media_attribute(
        ProductValueInterface $productValue,
        AttributeInterface $attribute
    ) {
        $productValue->getAttribute()->willReturn($attribute);
        $attribute->getBackendType()->willReturn('media');
        $this->supportsValue($productValue)->shouldReturn(true);

        $attribute->getBackendType()->willReturn('other');
        $this->supportsValue($productValue)->shouldReturn(false);
    }

    public function it_succesfully_checks_complete_media(
        ProductValueInterface $value,
        ChannelInterface $channel,
        LocaleInterface $locale,
        ProductMediaInterface $media
    ) {
        $value->getMedia()->willReturn(null);
        $this->isComplete($value, $channel, $locale)->shouldReturn(false);

        $value->getMedia()->willReturn([]);
        $this->isComplete($value, $channel, $locale)->shouldReturn(false);

        $media->__toString()->willReturn('');
        $value->getMedia()->willReturn($media);
        $this->isComplete($value, $channel, $locale)->shouldReturn(false);

        $media->__toString()->willReturn('other');
        $value->getMedia()->willReturn($media);
        $this->isComplete($value, $channel, $locale)->shouldReturn(true);
    }
}
