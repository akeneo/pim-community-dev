<?php

namespace spec\Akeneo\EnrichedEntity\Domain\Model\Record\Value;

use Akeneo\EnrichedEntity\Domain\Model\LocaleIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Record\Value\LocaleReference;
use PhpSpec\ObjectBehavior;

class LocaleReferenceSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedThrough('fromLocaleIdentifier', [LocaleIdentifier::fromCode('mobile')]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(LocaleReference::class);
    }

    function it_can_be_constructed_with_no_channel()
    {
        $this->beConstructedThrough('noLocale', []);
    }

    function it_normalizes_itself_when_instanciated_with_a_channel_identifier()
    {
        $this->normalize()->shouldReturn('mobile');
    }

    function it_normalizes_itself_when_instanciated_with_no_channel()
    {
        $this->beConstructedThrough('noLocale', []);
        $this->normalize()->shouldReturn(null);
    }

    function it_tells_if_is_equal_to_another_reference()
    {
        $this->equals(LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('mobile')))->shouldReturn(true);
        $this->equals(LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('print')))->shouldReturn(false);
        $this->equals(LocaleReference::noLocale())->shouldReturn(false);
    }

    function it_tells_if_is_equal_to_empty_reference()
    {
        $this->beConstructedThrough('noLocale', []);
        $this->equals(LocaleReference::noLocale())->shouldReturn(true);
        $this->equals(LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('print')))->shouldReturn(false);
    }
}
