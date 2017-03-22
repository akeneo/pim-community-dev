<?php

namespace spec\Pim\Component\Catalog\Completeness\Checker;

use Akeneo\Component\FileStorage\Model\FileInfo;
use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;

class MediaCompleteCheckerSpec extends ObjectBehavior
{
    public function it_is_a_completeness_checker()
    {
        $this->shouldImplement('Pim\Component\Catalog\Completeness\Checker\ProductValueCompleteCheckerInterface');
    }

    public function it_suports_media_attribute(
        ProductValueInterface $productValue,
        AttributeInterface $attribute,
        ChannelInterface $channel,
        LocaleInterface $locale
    ) {
        $productValue->getAttribute()->willReturn($attribute);
        $attribute->getBackendType()->willReturn('media');
        $this->supportsValue($productValue, $channel, $locale)->shouldReturn(true);

        $attribute->getBackendType()->willReturn('other');
        $this->supportsValue($productValue, $channel, $locale)->shouldReturn(false);
    }

    public function it_succesfully_checks_complete_media(
        ProductValueInterface $value,
        FileInfoInterface $media,
        ChannelInterface $channel,
        LocaleInterface $locale
    ) {
        $value->getData()->willReturn($media);
        $media->getKey()->willReturn('just-a-media');
        $this->isComplete($value, $channel, $locale)->shouldReturn(true);
    }

    public function it_checks_empty_value(
        ProductValueInterface $value,
        ChannelInterface $channel,
        LocaleInterface $locale
    ) {
        $value->getData()->willReturn(null);
        $this->isComplete($value, $channel, $locale)->shouldReturn(false);
    }

    public function it_checks_incomplete_media(
        ProductValueInterface $value,
        FileInfoInterface $media,
        ChannelInterface $channel,
        LocaleInterface $locale
    ) {
        $value->getData()->willReturn($media);

        $media->getKey()->willReturn(null);
        $this->isComplete($value, $channel, $locale)->shouldReturn(false);

        $media->getKey()->willReturn('');
        $this->isComplete($value, $channel, $locale)->shouldReturn(false);
    }
}
