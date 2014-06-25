<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Form\Comparator;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Pim\Bundle\CatalogBundle\Model;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use Doctrine\Common\Collections\ArrayCollection;

class OptionsComparatorSpec extends ObjectBehavior
{
    function let(
        Model\AbstractProductValue $value,
        Model\AbstractAttribute $attribute
    ) {
        $value->getAttribute()->willReturn($attribute);
        $value->getId()->willReturn(713705);
        $value->getScope()->willReturn('ecommerce');
        $value->getLocale()->willReturn('fr_FR');
        $attribute->getId()->willReturn(1337);
    }

    function it_is_a_comparator()
    {
        $this->shouldBeAnInstanceOf('PimEnterprise\Bundle\WorkflowBundle\Form\Comparator\ComparatorInterface');
    }

    function it_supports_multiselect_type(
        $value,
        $attribute
    ) {
        $value->getAttribute()->willReturn($attribute);
        $attribute->getAttributeType()->willReturn('pim_catalog_multiselect');

        $this->supportsComparison($value)->shouldBe(true);
    }

    function it_detects_changes_when_changing_options_data(
        $value,
        AttributeOption $red,
        AttributeOption $blue,
        AttributeOption $yellow,
        AttributeOption $green
    ){
        $submittedData = [
            'options' => '42,24,76',
        ];

        $options = new ArrayCollection([
            $red->getWrappedObject(),
            $blue->getWrappedObject(),
            $yellow->getWrappedObject(),
            $green->getWrappedObject(),
        ]);
        $red->getId()->willReturn(42);
        $blue->getId()->willReturn(76);
        $yellow->getId()->willReturn(54);
        $green->getId()->willReturn(24);

        $value->getOptions()->willReturn($options);

        $this->getChanges($value, $submittedData)->shouldReturn([
            'options' => '24,42,76',
            '__context__' => [
                'attribute_id' => 1337,
                'value_id' => 713705,
                'scope' => 'ecommerce',
                'locale' => 'fr_FR',
            ],
        ]);
    }

    function it_detects_no_changes_when_options_are_the_same(
        $value,
        AttributeOption $red,
        AttributeOption $blue,
        AttributeOption $yellow,
        AttributeOption $green
    ) {
        $submittedData = [
            'options' => '42,24,76,54',
        ];

        $options = new ArrayCollection([
            $red->getWrappedObject(),
            $blue->getWrappedObject(),
            $yellow->getWrappedObject(),
            $green->getWrappedObject(),
        ]);
        $red->getId()->willReturn(42);
        $blue->getId()->willReturn(76);
        $yellow->getId()->willReturn(54);
        $green->getId()->willReturn(24);

        $value->getOptions()->willReturn($options);

        $this->getChanges($value, $submittedData)->shouldReturn(null);
    }

    function it_detects_no_change_when_the_options_is_not_defined(
        $value
    ) {
        $submittedData = [];

        $this->getChanges($value, $submittedData)->shouldReturn(null);
    }
}
