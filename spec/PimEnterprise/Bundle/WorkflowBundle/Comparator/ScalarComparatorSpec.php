<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Comparator;

use PhpSpec\ObjectBehavior;

class ScalarComparatorSpec extends ObjectBehavior
{
    function it_is_a_comparator()
    {
        $this->shouldBeAnInstanceOf('PimEnterprise\Bundle\WorkflowBundle\Comparator\ComparatorInterface');
    }

    function it_supports_comparison()
    {
        $this->supportsComparison('pim_catalog_date')->shouldBe(true);
        $this->supportsComparison('pim_catalog_identifier')->shouldBe(true);
        $this->supportsComparison('pim_catalog_number')->shouldBe(true);
        $this->supportsComparison('pim_catalog_text')->shouldBe(true);
        $this->supportsComparison('pim_catalog_textarea')->shouldBe(true);
        $this->supportsComparison('pim_catalog_textarea')->shouldBe(true);
        $this->supportsComparison('other')->shouldBe(false);
    }

    function it_get_changes_when_adding_value()
    {
        $changes = ['value' => 'scalar', 'locale' => 'en_US', 'scope' => 'ecommerce'];
        $originals = [];

        $this->getChanges($changes, $originals)->shouldReturn($changes);
    }

    function it_get_changes_when_changing_value()
    {
        $changes   = ['value' => 'scalar', 'locale' => 'en_US', 'scope' => 'ecommerce'];
        $originals = ['value' => 'other scalar', 'locale' => 'en_US', 'scope' => 'ecommerce'];

        $this->getChanges($changes, $originals)->shouldReturn($changes);
    }

    function it_returns_null_when_values_are_the_same()
    {
        $changes   = ['value' => 'scalar', 'locale' => 'en_US', 'scope' => 'ecommerce'];
        $originals = ['value' => 'scalar', 'locale' => 'en_US', 'scope' => 'ecommerce'];

        $this->getChanges($changes, $originals)->shouldReturn(null);
    }
}
