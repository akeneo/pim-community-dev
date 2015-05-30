<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Comparator;

use PhpSpec\ObjectBehavior;

class BooleanComparatorSpec extends ObjectBehavior
{
    function it_is_a_comparator()
    {
        $this->shouldBeAnInstanceOf('PimEnterprise\Bundle\WorkflowBundle\Comparator\ComparatorInterface');
    }

    function it_supports_comparison()
    {
        $this->supportsComparison('pim_catalog_boolean')->shouldBe(true);
    }

    function it_get_changes_when_adding_value()
    {
        $changes = ['value' => true, 'locale' => 'en_US', 'scope' => 'ecommerce'];
        $originals = [];

        $this->getChanges($changes, $originals)->shouldReturn($changes);

        $changes = ['value' => 1, 'locale' => 'en_US', 'scope' => 'ecommerce'];
        $originals = [];

        $this->getChanges($changes, $originals)->shouldReturn($changes);
    }

    function it_get_changes_when_changing_value()
    {
        $changes   = ['value' => false, 'locale' => 'en_US', 'scope' => 'ecommerce'];
        $originals = ['value' => true, 'locale' => 'en_US', 'scope' => 'ecommerce'];

        $this->getChanges($changes, $originals)->shouldReturn($changes);

        $changes   = ['value' => 0, 'locale' => 'en_US', 'scope' => 'ecommerce'];
        $originals = ['value' => true, 'locale' => 'en_US', 'scope' => 'ecommerce'];

        $this->getChanges($changes, $originals)->shouldReturn($changes);
    }

    function it_returns_null_when_values_are_the_same()
    {
        $changes   = ['value' => 1, 'locale' => 'en_US', 'scope' => 'ecommerce'];
        $originals = ['value' => true, 'locale' => 'en_US', 'scope' => 'ecommerce'];

        $this->getChanges($changes, $originals)->shouldReturn(null);

        $changes   = ['value' => true, 'locale' => 'en_US', 'scope' => 'ecommerce'];
        $originals = ['value' => true, 'locale' => 'en_US', 'scope' => 'ecommerce'];

        $this->getChanges($changes, $originals)->shouldReturn(null);
    }
}
