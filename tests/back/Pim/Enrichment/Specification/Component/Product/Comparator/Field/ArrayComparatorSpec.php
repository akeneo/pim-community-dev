<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Comparator\Field;

use Akeneo\Pim\Enrichment\Component\Product\Comparator\ComparatorInterface;
use PhpSpec\ObjectBehavior;

class ArrayComparatorSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(['groups', 'categories', 'associations']);
    }

    function it_is_a_comparator()
    {
        $this->shouldBeAnInstanceOf(ComparatorInterface::class);
    }

    function it_supports_comparison()
    {
        $this->supports('groups')->shouldBe(true);
        $this->supports('categories')->shouldBe(true);
        $this->supports('associations')->shouldBe(true);
        $this->supports('other')->shouldBe(false);
    }

    function it_gets_changes_when_adding_value()
    {
        $changes = ['akeneo_tshirt'];
        $originals = null;
        $this->compare($changes, $originals)->shouldReturn($changes);
    }

    function it_gets_changes_when_changing_value()
    {
        $changes = ['akeneo_tshirt'];
        $originals = ['oro_tshirt'];
        $this->compare($changes, $originals)->shouldReturn($changes);
    }

    function it_returns_null_when_values_are_the_same()
    {
        $changes = ['akeneo_tshirt'];
        $originals = ['akeneo_tshirt'];
        $this->compare($changes, $originals)->shouldReturn(null);
    }

    function it_returns_null_when_values_are_the_same_but_not_ordered()
    {
        $changes = ['oro_tshirt', 'akeneo_tshirt'];
        $originals = ['akeneo_tshirt', 'oro_tshirt'];
        $this->compare($changes, $originals)->shouldReturn(null);
    }

    function it_returns_null_when_original_values_are_null()
    {
        $changes = ['akeneo_tshirt'];
        $originals = null;
        $this->compare($changes, $originals)->shouldReturn(['akeneo_tshirt']);
    }
}
