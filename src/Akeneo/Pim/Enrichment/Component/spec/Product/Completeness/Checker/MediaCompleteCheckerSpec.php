<?php

namespace spec\Akeneo\Pim\Enrichment\Component\Product\Completeness\Checker;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\Checker\ValueCompleteCheckerInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;

class MediaCompleteCheckerSpec extends ObjectBehavior
{
    public function it_is_a_completeness_checker()
    {
        $this->shouldImplement(ValueCompleteCheckerInterface::class);
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
