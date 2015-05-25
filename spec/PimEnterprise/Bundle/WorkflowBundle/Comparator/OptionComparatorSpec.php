<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Comparator;

use PhpSpec\ObjectBehavior;

class OptionComparatorSpec extends ObjectBehavior
{
    function it_is_a_comparator()
    {
        $this->shouldBeAnInstanceOf('PimEnterprise\Bundle\WorkflowBundle\Comparator\ComparatorInterface');
    }

    function it_supports_simpleselect_type()
    {
        $this->supportsComparison('pim_catalog_simpleselect')->shouldBe(true);
        $this->supportsComparison('pim_reference_data_simpleselect')->shouldBe(true);
    }

    function it_get_changes_when_adding_option_data()
    {
        $changes   = ['value' => ['code' => '42'], 'locale' => 'en_US', 'scope' => 'ecommerce'];
        $originals = [];

        $this->getChanges($changes, $originals)->shouldReturn([
            'locale' => 'en_US',
            'scope'  => 'ecommerce',
            'value'  => '42',
        ]);
    }

    function it_get_changes_when_changing_option_data()
    {
        $changes   = ['value' => ['code' => '42'], 'locale' => 'en_US', 'scope' => 'ecommerce'];
        $originals = ['value' => ['code' => '40']];

        $this->getChanges($changes, $originals)->shouldReturn([
            'locale' => 'en_US',
            'scope'  => 'ecommerce',
            'value'  => '42',
        ]);
    }

    function it_returns_null_when_option_is_the_same()
    {
        $changes   = ['value' => ['code' => '42'], 'locale' => 'en_US', 'scope' => 'ecommerce'];
        $originals = ['value' => ['code' => '42'], 'locale' => 'en_US', 'scope' => 'ecommerce'];

        $this->getChanges($changes, $originals)->shouldReturn(null);
    }
}
