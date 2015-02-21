<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Comparator;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model;
use Pim\Bundle\CatalogBundle\Model\AttributeOptionInterface;

class OptionComparatorSpec extends ObjectBehavior
{
    function let(
        Model\ProductValueInterface $value,
        Model\AttributeInterface $attribute
    ) {
        $value->getAttribute()->willReturn($attribute);
    }

    function it_is_a_comparator()
    {
        $this->shouldBeAnInstanceOf('PimEnterprise\Bundle\WorkflowBundle\Comparator\ComparatorInterface');
    }

    function it_supports_simpleselect_type($value, $attribute)
    {
        $attribute->getAttributeType()->willReturn('pim_catalog_simpleselect');

        $this->supportsComparison($value)->shouldBe(true);
    }

    function it_detects_changes_when_changing_option_data(
        $value,
        AttributeOptionInterface $red
    ) {
        $submittedData = [
            'option' => '42',
        ];

        $value->getOption()->willReturn($red);
        $red->getId()->willReturn(21);

        $this->getChanges($value, $submittedData)->shouldReturn([
            'option' => '42',
        ]);
    }

    function it_detects_changes_when_setting_for_the_first_time_a_value_option(
        $value
    ) {
        $submittedData = [
            'option' => '42',
        ];

        $value->getOption()->willReturn(null);

        $this->getChanges($value, $submittedData)->shouldReturn([
            'option' => '42',
        ]);
    }

    function it_detects_no_changes_when_option_is_the_same(
        Model\ProductValueInterface $value,
        AttributeOptionInterface $red
    ) {
        $submittedData = [
            'option' => '21',
        ];

        $value->getOption()->willReturn($red);
        $red->getId()->willReturn(21);

        $this->getChanges($value, $submittedData)->shouldReturn(null);
    }

    function it_detects_no_changes_when_setting_no_option_on_a_value_that_already_does_not_have_one(
        Model\ProductValueInterface $value
    ) {
        $submittedData = [
            'option' => '',
        ];

        $value->getOption()->willReturn(null);

        $this->getChanges($value, $submittedData)->shouldReturn(null);
    }

    function it_detects_no_change_when_the_option_is_not_defined(
        Model\ProductValueInterface $value
    ) {
        $submittedData = [];

        $this->getChanges($value, $submittedData)->shouldReturn(null);
    }
}
