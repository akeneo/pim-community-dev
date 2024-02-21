<?php

declare(strict_types=1);

namespace spec\Akeneo\Tool\Bundle\MeasureBundle\Model;

use Akeneo\Tool\Bundle\MeasureBundle\Model\LocaleIdentifier;
use PhpSpec\ObjectBehavior;

class LocaleIdentifierSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedThrough('fromCode', ['en_US']);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(LocaleIdentifier::class);
    }

    public function it_cannot_be_created_with_an_empty_string()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('fromCode', ['']);
    }

    public function it_can_normalize_itself()
    {
        $this->normalize()->shouldReturn('en_US');
    }

    public function it_tells_if_it_is_equals_to_another_locale_reference()
    {
        $this->equals(LocaleIdentifier::fromCode('en_US'))->shouldReturn(true);
        $this->equals(LocaleIdentifier::fromCode('fr_FR'))->shouldReturn(false);
    }
}
