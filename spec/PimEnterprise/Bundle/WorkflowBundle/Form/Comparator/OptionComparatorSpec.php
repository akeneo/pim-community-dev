<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Form\Comparator;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Pim\Bundle\CatalogBundle\Model;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;

class OptionComparatorSpec extends ObjectBehavior
{
    function it_is_a_comparator()
    {
        $this->shouldBeAnInstanceOf('PimEnterprise\Bundle\WorkflowBundle\Form\Comparator\ComparatorInterface');
    }

    function it_supports_simpleselect_type(
        Model\AbstractProductValue $value,
        Model\AbstractAttribute $attribute
    )
    {
        $value->getAttribute()->willReturn($attribute);
        $attribute->getAttributeType()->willReturn('pim_catalog_simpleselect');

        $this->supportsComparison($value)->shouldBe(true);
    }

    function it_detects_changes_when_changing_option_data(
        Model\AbstractProductValue $value,
        AttributeOption $red
    ){
        $submittedData = [
            'id' => '1',
            'option' => '42',
        ];

        $value->getOption()->willReturn($red);
        $red->getId()->willReturn(21);

        $this->getChanges($value, $submittedData)->shouldReturn([
            'id' => '1',
            'option' => '42',
        ]);
    }

    function it_detects_changes_when_setting_for_the_first_time_a_value_option(
        Model\AbstractProductValue $value
    ){
        $submittedData = [
            'id' => '1',
            'option' => '42',
        ];

        $value->getOption()->willReturn(null);

        $this->getChanges($value, $submittedData)->shouldReturn([
            'id' => '1',
            'option' => '42',
        ]);
    }

    function it_detects_no_changes_when_option_is_the_same(
        Model\AbstractProductValue $value,
        AttributeOption $red
    ) {
        $submittedData = [
            'id' => '1',
            'option' => '21',
        ];

        $value->getOption()->willReturn($red);
        $red->getId()->willReturn(21);

        $this->getChanges($value, $submittedData)->shouldReturn(null);
    }

    function it_detects_no_changes_when_setting_no_option_on_a_value_that_already_does_not_have_one(
        Model\AbstractProductValue $value
    ){
        $submittedData = [
            'id' => '1',
            'option' => '',
        ];

        $value->getOption()->willReturn(null);

        $this->getChanges($value, $submittedData)->shouldReturn(null);
    }
}
