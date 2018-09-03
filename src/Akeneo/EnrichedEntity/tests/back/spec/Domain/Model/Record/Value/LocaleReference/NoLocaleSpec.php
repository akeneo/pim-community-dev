<?php

namespace spec\Akeneo\EnrichedEntity\Domain\Model\Record\Value\LocaleReference;

use Akeneo\EnrichedEntity\Domain\Model\Record\Value\LocaleReference\LocaleIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\Record\Value\LocaleReference\NoLocale;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class NoLocaleSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedThrough('create', []);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(NoLocale::class);
    }

    function it_normalizes_itself()
    {
        $this->normalize()->shouldReturn(null);
    }

    function it_tells_if_it_is_equal_to_another_reference()
    {
        $this->equals(NoLocale::create())->shouldReturn(true);
        $this->equals(LocaleIdentifier::fromCode('name_designer_fingerprint'))->shouldReturn(false);
    }
}
