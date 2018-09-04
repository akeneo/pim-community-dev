<?php

declare(strict_types=1);

namespace spec\Akeneo\EnrichedEntity\Domain\Query\Attribute;

use Akeneo\EnrichedEntity\Domain\Query\Attribute\ValueKey;
use PhpSpec\ObjectBehavior;

class ValueKeySpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedThrough('fromNormalized', ['name_brand_AE7F6A76E5F_mobile_en_US']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ValueKey::class);
    }

    function it_is_created_from_normalized_data()
    {
        $this->shouldBeAnInstanceOf(ValueKey::class);
    }

    function it_normalizes_itself()
    {
        $this->normalize()->shouldReturn('name_brand_AE7F6A76E5F_mobile_en_US');
    }

    function it_cannot_be_created_with_an_empty_string()
    {
        $this->beConstructedThrough('fromNormalized', ['']);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
