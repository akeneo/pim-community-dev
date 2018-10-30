<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Comparator\Field;

use Akeneo\Pim\Enrichment\Component\Product\Comparator\ComparatorInterface;
use PhpSpec\ObjectBehavior;

class BooleanComparatorSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(['enabled']);
    }

    function it_is_a_comparator()
    {
        $this->shouldBeAnInstanceOf(ComparatorInterface::class);
    }

    function it_supports_comparison()
    {
        $this->supports('enabled')->shouldBe(true);
        $this->supports('other')->shouldBe(false);
    }

    function it_gets_changes_when_adding_value()
    {
        $changes = true;
        $originals = null;
        $this->compare($changes, $originals)->shouldReturn($changes);

        $changes = false;
        $originals = null;
        $this->compare($changes, $originals)->shouldReturn($changes);

        $changes = 1;
        $this->compare($changes, $originals)->shouldReturn($changes);
    }

    function it_gets_changes_when_changing_value()
    {
        $changes = false;
        $originals = true;
        $this->compare($changes, $originals)->shouldReturn($changes);

        $changes = 0;
        $this->compare($changes, $originals)->shouldReturn($changes);
    }

    function it_returns_null_when_values_are_the_same()
    {
        $changes = true;
        $originals = true;
        $this->compare($changes, $originals)->shouldReturn(null);

        $changes = 1;
        $originals = 1;
        $this->compare($changes, $originals)->shouldReturn(null);

        $changes = 1;
        $originals = true;
        $this->compare($changes, $originals)->shouldReturn(null);

        $changes = true;
        $originals = 1;
        $this->compare($changes, $originals)->shouldReturn(null);
    }
}
