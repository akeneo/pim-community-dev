<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation;

use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Target;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\TransformationReference;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeAllowedExtensions;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeDecimalsAllowed;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsReadOnly;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeLimit;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeMaxFileSize;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFile\MediaType;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFileAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\NumberAttribute;
use Akeneo\AssetManager\Domain\Model\ChannelIdentifier;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifier;
use PhpSpec\ObjectBehavior;

class TargetSpec extends ObjectBehavior
{
    function it_only_accepts_media_file_attributes(NumberAttribute $attribute)
    {
        $attribute = NumberAttribute::create(
            AttributeIdentifier::fromString('a_number'),
            AssetFamilyIdentifier::fromString('my_asset_family'),
            AttributeCode::fromString('my_target'),
            LabelCollection::fromArray([]),
            AttributeOrder::fromInteger(1),
            AttributeIsRequired::fromBoolean(false),
            AttributeIsReadOnly::fromBoolean(false),
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
                $this->createMediaFileAttribute(false, false),
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
                $this->createMediaFileAttribute(false, true),
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
                $this->createMediaFileAttribute(true, false),
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
                $this->createMediaFileAttribute(true, true),
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
                $this->createMediaFileAttribute(false, false),
                ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('ecommerce')),
                LocaleReference::noReference()
            ]
        );
        $this->shouldThrow(new \InvalidArgumentException('Attribute "my_target" is not scopable, you cannot define a channel'))->duringInstantiation();
    }

    function it_throws_an_exception_when_not_providing_a_channel_with_a_scopable_attribute()
    {
        $this->beConstructedThrough(
            'create',
            [
                $this->createMediaFileAttribute(true, false),
                ChannelReference::noReference(),
                LocaleReference::noReference()
            ]
        );
        $this->shouldThrow(new \InvalidArgumentException('Attribute "my_target" is scopable, you must define a channel'))->duringInstantiation();
    }

    function it_throws_an_exception_when_providing_a_locale_with_a_non_localizable_attribute()
    {
        $this->beConstructedThrough(
            'create',
            [
                $this->createMediaFileAttribute(false, false),
                ChannelReference::noReference(),
                LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
            ]
        );
        $this->shouldThrow(new \InvalidArgumentException('Attribute "my_target" is not localizable, you cannot define a locale'))->duringInstantiation();
    }

    function it_throws_an_exception_when_not_providing_a_locale_with_a_localizable_attribute()
    {
        $this->beConstructedThrough(
            'create',
            [
                $this->createMediaFileAttribute(false, true),
                ChannelReference::noReference(),
                LocaleReference::noReference(),
            ]
        );
        $this->shouldThrow(new \InvalidArgumentException('Attribute "my_target" is localizable, you must define a locale'))->duringInstantiation();
    }

    function it_equals_localizable_and_scopable_attribute(TransformationReference $reference)
    {
        $this->beConstructedThrough(
            'create',
            [
                $this->createMediaFileAttribute(true, true),
                ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('ecommerce')),
                LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
            ]
        );

        $reference->getAttributeCode()->willReturn(AttributeCode::fromString('my_target'));
        $reference->getChannelReference()->willReturn(ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('ecommerce')));
        $reference->getLocaleReference()->willReturn(LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')));

        $this->equals($reference)->shouldReturn(true);
    }

    function it_does_not_equal_localizable_and_scopable_attribute(TransformationReference $reference)
    {
        $this->beConstructedThrough(
            'create',
            [
                $this->createMediaFileAttribute(true, true),
                ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('ecommerce')),
                LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
            ]
        );

        $reference->getAttributeCode()->willReturn(AttributeCode::fromString('my_target'));
        $reference->getChannelReference()->willReturn(ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('ecommerce')));
        $reference->getLocaleReference()->willReturn(LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')));

        $this->equals($reference)->shouldReturn(false);
    }

    function it_normalizes_a_target()
    {
        $this->beConstructedThrough(
            'create',
            [
                $this->createMediaFileAttribute(true, true),
                ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('ecommerce')),
                LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
            ]
        );

        $this->normalize()->shouldReturn([
            'attribute' => 'my_target',
            'channel' => 'ecommerce',
            'locale' => 'en_US'
        ]);
    }

    function it_can_be_instantiated_from_normalized_format()
    {
        $normalizedSource = [
            'attribute' => 'my_target',
            'channel' => 'ecommerce',
            'locale' => 'en_US',
        ];
        $this->beConstructedThrough('createFromNormalized', [$normalizedSource]);

        $comparedTarget = Target::create(
            $this->createMediaFileAttribute(true, true),
            ChannelReference::createFromNormalized('ecommerce'),
            LocaleReference::createFromNormalized('en_US')
        );

        $this->shouldBeLike($comparedTarget);
    }

    private function createMediaFileAttribute(bool $scopable, bool $localizable): MediaFileAttribute
    {
        return MediaFileAttribute::create(
            AttributeIdentifier::fromString('image_identifier'),
            AssetFamilyIdentifier::fromString('my_asset_family'),
            AttributeCode::fromString('my_target'),
            LabelCollection::fromArray([]),
            AttributeOrder::fromInteger(1),
            AttributeIsRequired::fromBoolean(false),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean($scopable),
            AttributeValuePerLocale::fromBoolean($localizable),
            AttributeMaxFileSize::noLimit(),
            AttributeAllowedExtensions::fromList(AttributeAllowedExtensions::ALL_ALLOWED),
            MediaType::fromString(MediaType::IMAGE)
        );
    }
}
