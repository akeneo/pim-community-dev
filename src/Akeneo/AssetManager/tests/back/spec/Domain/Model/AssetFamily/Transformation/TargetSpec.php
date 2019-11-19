<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation;

use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeAllowedExtensions;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeDecimalsAllowed;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeLimit;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeMaxFileSize;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\AssetManager\Domain\Model\Attribute\ImageAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\NumberAttribute;
use Akeneo\AssetManager\Domain\Model\ChannelIdentifier;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifier;
use PhpSpec\ObjectBehavior;

class TargetSpec extends ObjectBehavior
{
    function it_only_accepts_image_attributes(NumberAttribute $attribute)
    {
        $attribute = NumberAttribute::create(
            AttributeIdentifier::fromString('a_number'),
            AssetFamilyIdentifier::fromString('my_asset_family'),
            AttributeCode::fromString('my_target'),
            LabelCollection::fromArray([]),
            AttributeOrder::fromInteger(1),
            AttributeIsRequired::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeDecimalsAllowed::fromBoolean(false),
            AttributeLimit::limitless(),
            AttributeLimit::limitless()
        );
        $this->beConstructedThrough('create', [$attribute, ChannelReference::noReference(), LocaleReference::noReference()]);

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_creates_a_target_for_a_non_scopable_non_localizable_attribute()
    {
        $this->beConstructedThrough(
            'create',
            [
                $this->createImageAttribute(false, false),
                ChannelReference::noReference(),
                LocaleReference::noReference()
            ]
        );
        $this->getWrappedObject();
    }

    function it_creates_a_target_for_a_non_scopable_and_localizable_attribute()
    {
        $this->beConstructedThrough(
            'create',
            [
                $this->createImageAttribute(false, true),
                ChannelReference::noReference(),
                LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US'))
            ]
        );
        $this->getWrappedObject();
    }

    function it_creates_a_target_for_a_scopable_non_localizable_attribute()
    {
        $this->beConstructedThrough(
            'create',
            [
                $this->createImageAttribute(true, false),
                ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('ecommerce')),
                LocaleReference::noReference()
            ]
        );
        $this->getWrappedObject();
    }

    function it_creates_a_target_for_a_scopable_localizable_attribute()
    {
        $this->beConstructedThrough(
            'create',
            [
                $this->createImageAttribute(true, true),
                ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('tablet')),
                LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR'))
            ]
        );
        $this->getWrappedObject();
    }

    function it_throws_an_exception_when_providing_a_channel_with_a_non_scopable_attribute()
    {
        $this->beConstructedThrough(
            'create',
            [
                $this->createImageAttribute(false, false),
                ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('ecommerce')),
                LocaleReference::noReference()
            ]
        );
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_throws_an_exception_when_not_providing_a_channel_with_a_scopable_attribute()
    {
        $this->beConstructedThrough(
            'create',
            [
                $this->createImageAttribute(true, false),
                ChannelReference::noReference(),
                LocaleReference::noReference()
            ]
        );
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_throws_an_exception_when_providing_a_locale_with_a_non_localizable_attribute()
    {
        $this->beConstructedThrough(
            'create',
            [
                $this->createImageAttribute(false, false),
                ChannelReference::noReference(),
                LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
            ]
        );
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_throws_an_exception_when_not_providing_a_locale_with_a_localizable_attribute()
    {
        $this->beConstructedThrough(
            'create',
            [
                $this->createImageAttribute(false, true),
                ChannelReference::noReference(),
                LocaleReference::noReference(),
            ]
        );
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    private function createImageAttribute(bool $scopable, bool $localizable): ImageAttribute
    {
        return ImageAttribute::create(
            AttributeIdentifier::fromString('image_identifier'),
            AssetFamilyIdentifier::fromString('my_asset_family'),
            AttributeCode::fromString('my_target'),
            LabelCollection::fromArray([]),
            AttributeOrder::fromInteger(1),
            AttributeIsRequired::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean($scopable),
            AttributeValuePerLocale::fromBoolean($localizable),
            AttributeMaxFileSize::noLimit(),
            AttributeAllowedExtensions::fromList(AttributeAllowedExtensions::ALL_ALLOWED)
        );
    }
}
