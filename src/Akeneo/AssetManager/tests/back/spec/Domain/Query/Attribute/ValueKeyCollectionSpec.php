<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Domain\Query\Attribute;

use Akeneo\AssetManager\Domain\Query\Attribute\ValueKey;
use Akeneo\AssetManager\Domain\Query\Attribute\ValueKeyCollection;
use PhpSpec\ObjectBehavior;

class ValueKeyCollectionSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedThrough('fromValueKeys', [
            [
                ValueKey::createFromNormalized('viande'),
                ValueKey::createFromNormalized('picklerick'),
            ],
        ]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ValueKeyCollection::class);
    }

    function it_is_created_from_an_array_of_value_keys()
    {
        $this->shouldBeAnInstanceOf(ValueKeyCollection::class);
    }

    function it_cannot_be_created_from_anything_else()
    {
        $this->beConstructedThrough('fromValueKeys', [['hello', 134]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_normalizes_itself()
    {
        $this->normalize()->shouldReturn([
            'viande',
            'picklerick',
        ]);
    }
}
