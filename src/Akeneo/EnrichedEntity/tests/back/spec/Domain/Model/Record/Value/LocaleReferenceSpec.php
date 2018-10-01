<?php

namespace spec\Akeneo\ReferenceEntity\Domain\Model\Record\Value;

use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\LocaleReference;
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

    function it_can_be_constructed_with_no_locale()
    {
        $this->beConstructedThrough('noReference', []);
    }

    function it_can_be_constructed_with_a_locale_code()
    {
        $this->beConstructedThrough('createFromNormalized', ['fr_FR']);
        $this->normalize()->shouldReturn('fr_FR');
    }

    function it_can_be_constructed_with_no_locale_code()
    {
        $this->beConstructedThrough('createFromNormalized', [null]);
        $this->normalize()->shouldReturn(null);
    }

    function it_normalizes_itself_when_instanciated_with_a_locale_identifier()
    {
        $this->normalize()->shouldReturn('mobile');
    }

    function it_normalizes_itself_when_instanciated_with_no_locale()
    {
        $this->beConstructedThrough('noReference', []);
        $this->normalize()->shouldReturn(null);
    }

    function it_tells_if_is_equal_to_another_reference()
    {
        $this->equals(LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('mobile')))->shouldReturn(true);
        $this->equals(LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('print')))->shouldReturn(false);
        $this->equals(LocaleReference::noReference())->shouldReturn(false);
    }

    function it_tells_if_is_equal_to_empty_reference()
    {
        $this->beConstructedThrough('noReference', []);
        $this->equals(LocaleReference::noReference())->shouldReturn(true);
        $this->equals(LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('print')))->shouldReturn(false);
    }
}
