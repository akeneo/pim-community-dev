<?php

namespace spec\Pim\Component\Catalog\Comparator\Field;

use PhpSpec\ObjectBehavior;

class ScalarComparatorSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(['family', 'variant_group']);
    }

    function it_is_a_comparator()
    {
        $this->shouldBeAnInstanceOf('Pim\Component\Catalog\Comparator\ComparatorInterface');
    }

    function it_supports_comparison()
    {
        $this->supports('family')->shouldBe(true);
        $this->supports('variant_group')->shouldBe(true);
        $this->supports('other')->shouldBe(false);
    }

    function it_gets_changes_when_adding_value()
    {
        $changes = 'tshirt';
        $originals = null;
        $this->compare($changes, $originals)->shouldReturn($changes);
    }

    function it_gets_changes_when_changing_value()
    {
        $changes   = 'tshirt';
        $originals = 'camera';
        $this->compare($changes, $originals)->shouldReturn($changes);
    }

    function it_returns_null_when_values_are_the_same()
    {
        $changes   = 'tshirt';
        $originals = 'tshirt';
        $this->compare($changes, $originals)->shouldReturn(null);
    }
}
