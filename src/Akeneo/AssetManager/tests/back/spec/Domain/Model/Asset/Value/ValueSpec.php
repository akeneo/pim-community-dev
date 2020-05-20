<?php

namespace spec\Akeneo\AssetManager\Domain\Model\Asset\Value;

use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\ChannelIdentifier;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifier;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\EmptyData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\TextData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\Value;
use PhpSpec\ObjectBehavior;

class ValueSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedThrough('create', [
            AttributeIdentifier::fromString('name_designer_fingerprint'),
            ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('mobile')),
            LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
            TextData::fromString('Starck'),
        ]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Value::class);
    }

    function it_tells_if_it_is_not_empty()
    {
        $this->isEmpty()->shouldReturn(false);
    }

    function it_tells_if_it_is_empty()
    {
        $this->beConstructedThrough('create', [
            AttributeIdentifier::fromString('name_designer_fingerprint'),
            ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('mobile')),
            LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
            EmptyData::create(),
        ]);
        $this->isEmpty()->shouldReturn(true);
    }

    function it_tells_if_it_has_a_value_for_a_particular_channel()
    {
        $this->hasChannel()->shouldReturn(true);
    }

    function it_tells_if_it_does_not_have_a_value_for_a_particular_channel()
    {
        $this->beConstructedThrough('create', [
            AttributeIdentifier::fromString('name_designer_fingerprint'),
            ChannelReference::noReference(),
            LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
            TextData::fromString('Starck'),
        ]);
        $this->hasChannel()->shouldReturn(false);
    }

    function it_tells_if_it_has_a_value_for_a_particular_locale()
    {
        $this->hasLocale()->shouldReturn(true);
    }

    function it_tells_if_it_does_not_have_a_value_for_a_particular_locale()
    {
        $this->beConstructedThrough('create', [
            AttributeIdentifier::fromString('name_designer_fingerprint'),
            ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('mobile')),
            LocaleReference::noReference(),
            TextData::fromString('Starck'),
        ]);
        $this->hasLocale()->shouldReturn(false);
    }

    function it_tells_if_two_values_have_the_same_attribute()
    {
        $this->sameAttribute(Value::create(
            AttributeIdentifier::fromString('name_designer_fingerprint'),
            ChannelReference::noReference(),
            LocaleReference::noReference(),
            TextData::fromString('Starck')
        ))->shouldReturn(true);

        $this->sameAttribute(Value::create(
            AttributeIdentifier::fromString('another_attribute'),
            ChannelReference::noReference(),
            LocaleReference::noReference(),
            TextData::fromString('Starck')
        ))->shouldReturn(false);
    }

    function it_tells_if_two_values_have_the_same_channel()
    {
        $this->sameChannel(Value::create(
            AttributeIdentifier::fromString('another_attribute'),
            ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('mobile')),
            LocaleReference::noReference(),
            TextData::fromString('Starck')
        ))->shouldReturn(true);

        $this->sameChannel(Value::create(
            AttributeIdentifier::fromString('another_attribute'),
            ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('another_channel')),
            LocaleReference::noReference(),
            TextData::fromString('Starck')
        ))->shouldReturn(false);
        $this->sameChannel(Value::create(
            AttributeIdentifier::fromString('another_attribute'),
            ChannelReference::noReference(),
            LocaleReference::noReference(),
            TextData::fromString('Starck')
        ))->shouldReturn(false);
    }

    function it_tells_if_two_values_have_the_same_locale()
    {
        $this->sameLocale(Value::create(
            AttributeIdentifier::fromString('another_attribute'),
            ChannelReference::noReference(),
            LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
            TextData::fromString('Starck')
        ))->shouldReturn(true);

        $this->sameLocale(Value::create(
            AttributeIdentifier::fromString('another_attribute'),
            ChannelReference::noReference(),
            LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('another_locale')),
            TextData::fromString('Starck')
        ))->shouldReturn(false);
        $this->sameLocale(Value::create(
            AttributeIdentifier::fromString('another_attribute'),
            ChannelReference::noReference(),
            LocaleReference::noReference(),
            TextData::fromString('Starck')
        ))->shouldReturn(false);
    }

    function it_tell_if_two_values_are_equals()
    {
        $this->equals(
            Value::create(
                AttributeIdentifier::fromString('name_designer_fingerprint'),
                ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('mobile')),
                LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                TextData::fromString('Starck'),
            ),
        )->shouldReturn(true);

        $this->equals(
            Value::create(
                AttributeIdentifier::fromString('name_designer_fingerprint'),
                ChannelReference::noReference(),
                LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                TextData::fromString('Starck'),
            ),
        )->shouldReturn(false);

        $this->equals(
            Value::create(
                AttributeIdentifier::fromString('name_designer_fingerprint'),
                ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('mobile')),
                LocaleReference::noReference(),
                TextData::fromString('Starck'),
            )
        )->shouldReturn(false);

        $this->equals(
            Value::create(
                AttributeIdentifier::fromString('another_attribute'),
                ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('mobile')),
                LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                TextData::fromString('Starck'),
            ),
        )->shouldReturn(false);

        $this->equals(
            Value::create(
                AttributeIdentifier::fromString('another_attribute'),
                ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('mobile')),
                LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                TextData::fromString('Starcks'),
            ),
        )->shouldReturn(false);
    }

    function it_normalizes_itself()
    {
        $this->normalize()->shouldReturn([
                'attribute' => 'name_designer_fingerprint',
                'channel'   => 'mobile',
                'locale'    => 'fr_FR',
                'data'      => 'Starck',
            ]
        );
    }
}
