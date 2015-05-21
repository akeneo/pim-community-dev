<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Comparator;

use PhpSpec\ObjectBehavior;

class OptionsComparatorSpec extends ObjectBehavior
{
    function it_is_a_comparator()
    {
        $this->shouldBeAnInstanceOf('PimEnterprise\Bundle\WorkflowBundle\Comparator\AttributeComparatorInterface');
    }

    function it_supports_multiselect_type()
    {
        $this->supportsComparison('pim_catalog_multiselect')->shouldBe(true);
        $this->supportsComparison('pim_reference_data_multiselect')->shouldBe(true);
        $this->supportsComparison('other')->shouldBe(false);
    }

    function it_get_changes_when_adding_options_data()
    {
        $changes   = ['value' => [['code' => '42'], ['code' => '43']], 'locale' => 'en_US', 'scope' => 'ecommerce'];
        $originals = [];

        $this->getChanges($changes, $originals)->shouldReturn([
            'locale' => 'en_US',
            'scope'  => 'ecommerce',
            'value' => ['42', '43'],
        ]);
    }

    function it_get_changes_when_changing_options_data()
    {
        $changes   = ['value' => [
            ['code' => '42'],
            ['code' => '43'],
            ['code' => '45']
        ], 'locale' => 'en_US', 'scope' => 'ecommerce'];
        $originals = ['value' => [['code' => '42'], ['code' => '44']]];

        $this->getChanges($changes, $originals)->shouldReturn([
            'locale' => 'en_US',
            'scope'  => 'ecommerce',
            'value' => ['43', '45'],
        ]);
    }

    function it_returns_null_when_option_is_the_same()
    {
        $changes   = ['value' => [['code' => '42'], ['code' => '44']], 'locale' => 'en_US', 'scope' => 'ecommerce'];
        $originals = ['value' => [['code' => '42'], ['code' => '44']], 'locale' => 'en_US', 'scope' => 'ecommerce'];

        $this->getChanges($changes, $originals)->shouldReturn(null);
    }
}
