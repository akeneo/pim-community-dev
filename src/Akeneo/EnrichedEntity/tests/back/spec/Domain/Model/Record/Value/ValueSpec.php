<?php

namespace spec\Akeneo\ReferenceEntity\Domain\Model\Record\Value;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ChannelIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ChannelReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\EmptyData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\LocaleReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\TextData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\Value;
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
