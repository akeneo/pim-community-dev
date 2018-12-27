<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Domain\Model\Permission;

use Akeneo\ReferenceEntity\Domain\Model\Permission\RightLevel;
use PhpSpec\ObjectBehavior;

class RightLevelSpec extends ObjectBehavior
{
    function it_can_directly_construct_edit_right_level()
    {
        $this->beConstructedThrough('edit');

        $this->normalize()->shouldReturn('edit');
    }

    function it_can_directly_construct_view_right_level()
    {
        $this->beConstructedThrough('edit');

        $this->normalize()->shouldReturn('edit');
    }

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

    function it_can_compare_itself()
    {
        $this->beConstructedThrough('edit');
        $this->equals(RightLevel::edit())->shouldReturn(true);
        $this->equals(RightLevel::view())->shouldReturn(false);
    }
}
