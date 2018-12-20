<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Domain\Model\Permission;

use PhpSpec\ObjectBehavior;

class RightLevelSpec extends ObjectBehavior
{
    function it_normalizes_itself()
    {
        $this->beConstructedThrough('fromString', ['view']);

        $this->normalize()->shouldReturn('view');
    }

    function it_throws_an_exception_for_invalid_levels()
    {
        $this->beConstructedThrough('fromString', ['toto']);
        $this->shouldThrow('InvalidArgumentException')->duringInstantiation();
    }
}
