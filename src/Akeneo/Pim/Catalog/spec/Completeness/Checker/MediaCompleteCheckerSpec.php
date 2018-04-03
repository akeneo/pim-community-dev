<?php

namespace spec\Pim\Component\Catalog\Completeness\Checker;

use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ValueInterface;

class MediaCompleteCheckerSpec extends ObjectBehavior
{
    public function it_is_a_completeness_checker()
    {
        $this->shouldImplement('Pim\Component\Catalog\Completeness\Checker\ValueCompleteCheckerInterface');
    }

    public function it_suports_media_attribute(
        ValueInterface $value,
        AttributeInterface $attribute,
        ChannelInterface $channel,
        LocaleInterface $locale
    ) {
        $value->getAttribute()->willReturn($attribute);
        $attribute->getBackendType()->willReturn('media');
        $this->supportsValue($value, $channel, $locale)->shouldReturn(true);

        $attribute->getBackendType()->willReturn('other');
        $this->supportsValue($value, $channel, $locale)->shouldReturn(false);
    }

    public function it_succesfully_checks_complete_media(
        ValueInterface $value,
        FileInfoInterface $media,
        ChannelInterface $channel,
        LocaleInterface $locale
    ) {
        $value->getData()->willReturn($media);
        $media->getKey()->willReturn('just-a-media');
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

    public function it_checks_incomplete_media(
        ValueInterface $value,
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
