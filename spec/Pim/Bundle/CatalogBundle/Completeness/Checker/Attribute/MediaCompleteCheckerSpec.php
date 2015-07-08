<?php

namespace spec\Pim\Bundle\CatalogBundle\Completeness\Checker\Attribute;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Model\ProductMediaInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;

class MediaCompleteCheckerSpec extends ObjectBehavior
{
    public function it_suports_media_attribute(
        AttributeInterface $attribute
    ) {
        $attribute->getBackendType()->willReturn('media');
        $this->supportsAttribute($attribute)->shouldReturn(true);

        $attribute->getBackendType()->willReturn('other');
        $this->supportsAttribute($attribute)->shouldReturn(false);
    }

    public function it_succesfully_checks_complete_media(
        ProductValueInterface $value,
        ChannelInterface $channel,
        ProductMediaInterface $media
    ) {
        $value->getMedia()->willReturn(null);
        $this->isComplete($value, $channel, 'en_US')->shouldReturn(false);

        $value->getMedia()->willReturn([]);
        $this->isComplete($value, $channel, 'en_US')->shouldReturn(false);

        $media->__toString()->willReturn('');
        $value->getMedia()->willReturn($media);
        $this->isComplete($value, $channel, 'en_US')->shouldReturn(false);

        $media->__toString()->willReturn('other');
        $value->getMedia()->willReturn($media);
        $this->isComplete($value, $channel, 'en_US')->shouldReturn(true);
    }
}
