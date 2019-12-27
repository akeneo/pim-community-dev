<?php

namespace spec\Akeneo\AssetManager\Domain\Model\AssetFamily\NamingConvention;

use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
use Akeneo\AssetManager\Domain\Model\AssetFamily\NamingConvention\Source;
use Akeneo\AssetManager\Domain\Model\ChannelIdentifier;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifier;
use PhpSpec\ObjectBehavior;

class SourceSpec extends ObjectBehavior
{
    function it_is_a_source()
    {
        $this->beConstructedThrough('createFromNormalized', [['property' => 'code']]);
        $this->shouldHaveType(Source::class);
    }

    function it_cannot_be_constructed_without_specifying_a_property()
    {
        $this->beConstructedThrough('createFromNormalized', [[]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_cannot_be_constructed_with_an_empty_property()
    {
        $this->beConstructedThrough('createFromNormalized', [['property' => '']]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_can_be_constructed_without_channel_nor_locale()
    {
        $this->beConstructedThrough('createFromNormalized', [['property' => 'image']]);
        $this->getChannelReference()->shouldBeLike(ChannelReference::noReference());
        $this->getLocaleReference()->shouldBeLike(LocaleReference::noReference());
    }

    function it_can_be_constructed_with_a_channel()
    {
        $this->beConstructedThrough('createFromNormalized', [['property' => 'image', 'channel' => 'ecommerce']]);
        $this->getChannelReference()->shouldBeLike(
            ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('ecommerce'))
        );
        $this->getLocaleReference()->shouldBeLike(LocaleReference::noReference());
    }

    function it_can_be_constructed_with_a_locale()
    {
        $this->beConstructedThrough('createFromNormalized', [['property' => 'image', 'locale' => 'en_US']]);
        $this->getLocaleReference()->shouldBeLike(
            LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US'))
        );
        $this->getChannelReference()->shouldBeLike(ChannelReference::noReference());
    }

    function it_can_be_constructed_with_both_a_channel_and_a_locale()
    {
        $this->beConstructedThrough(
            'createFromNormalized',
            [['property' => 'image', 'channel' => 'mobile', 'locale' => 'fr_FR']]
        );
        $this->getChannelReference()->shouldBeLike(
            ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('mobile'))
        );
        $this->getLocaleReference()->shouldBeLike(
            LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR'))
        );
    }

    function it_can_be_normalized()
    {
        $this->beConstructedThrough('createFromNormalized', [['property' => 'image', 'locale' => 'en_US']]);
        $this->normalize()->shouldReturn(
            [
                'property' => 'image',
                'channel' => null,
                'locale' => 'en_US',
            ]
        );
    }
}
