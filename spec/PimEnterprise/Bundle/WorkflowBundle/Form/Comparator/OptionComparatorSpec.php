<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Form\Comparator;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Pim\Bundle\CatalogBundle\Model;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;

class OptionComparatorSpec extends ObjectBehavior
{
    function let(
        Model\AbstractProductValue $value,
        Model\AbstractAttribute $attribute
    ) {
        $value->getAttribute()->willReturn($attribute);
        $value->getId()->willReturn(713705);
        $value->getScope()->willReturn('ecommerce');
        $attribute->getId()->willReturn(1337);
    }

    function it_is_a_comparator()
    {
        $this->shouldBeAnInstanceOf('PimEnterprise\Bundle\WorkflowBundle\Form\Comparator\ComparatorInterface');
    }

    function it_supports_simpleselect_type($value, $attribute) {
        $attribute->getAttributeType()->willReturn('pim_catalog_simpleselect');

        $this->supportsComparison($value)->shouldBe(true);
    }

    function it_detects_changes_when_changing_option_data(
        $value,
        AttributeOption $red
    ){
        $submittedData = [
            'option' => '42',
        ];

        $value->getOption()->willReturn($red);
        $red->getId()->willReturn(21);

        $this->getChanges($value, $submittedData)->shouldReturn([
            'option' => '42',
            '__context__' => [
                'attribute_id' => 1337,
                'value_id' => 713705,
                'scope' => 'ecommerce',
            ],
        ]);
    }

    function it_detects_changes_when_setting_for_the_first_time_a_value_option(
        $value
    ){
        $submittedData = [
            'option' => '42',
        ];

        $value->getOption()->willReturn(null);

        $this->getChanges($value, $submittedData)->shouldReturn([
            'option' => '42',
            '__context__' => [
                'attribute_id' => 1337,
                'value_id' => 713705,
                'scope' => 'ecommerce',
            ],
        ]);
    }

    function it_detects_no_changes_when_option_is_the_same(
        Model\AbstractProductValue $value,
        AttributeOption $red
    ) {
        $submittedData = [
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
            'option' => '',
        ];

        $value->getOption()->willReturn(null);

        $this->getChanges($value, $submittedData)->shouldReturn(null);
    }

    function it_detects_no_change_when_the_option_is_not_defined(
        Model\AbstractProductValue $value
    ) {
        $submittedData = [];

        $this->getChanges($value, $submittedData)->shouldReturn(null);
    }
}
