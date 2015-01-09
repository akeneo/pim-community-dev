<?php

namespace spec\Pim\Bundle\CatalogBundle\AttributeType;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\AttributeType\AbstractAttributeType;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Validator\ConstraintGuesserInterface;
use Prophecy\Argument;
use Symfony\Component\Form\FormFactory;

class NumberTypeSpec extends ObjectBehavior
{
    function let(ConstraintGuesserInterface $guesser, AttributeInterface $attribute, ProductValueInterface $value)
    {
        $value->getAttribute()->willReturn($attribute);

        $this->beConstructedWith(AbstractAttributeType::BACKEND_TYPE_DECIMAL, 'pim_number', $guesser);
    }

    function it_builds_attributes_form_types(FormFactory $factory, $attribute)
    {
        $attribute->getId()->willReturn(42);
        $attribute->getProperties()->willReturn([]);
        $attribute->setProperty(Argument::any(), Argument::any())->shouldBeCalled();
        $attribute->isDecimalsAllowed()->willReturn(true);

        $this->buildAttributeFormTypes($factory, $attribute)->shouldHaveCount(8);
    }

    function it_prepares_the_product_value_form($value, $attribute)
    {
        $attribute->getBackendType()->willReturn(AbstractAttributeType::BACKEND_TYPE_DECIMAL);
        $this->prepareValueFormName($value)->shouldReturn(AbstractAttributeType::BACKEND_TYPE_DECIMAL);
    }

    function it_prepares_the_product_value_form_alias($value)
    {
        $this->prepareValueFormAlias($value)->shouldReturn('pim_number');
    }

    function it_prepares_value_form_options($value, $attribute)
    {
        $attribute->getLabel()->willReturn('Some label');
        $attribute->isRequired()->willReturn(true);
        $attribute->isDecimalsAllowed()->willReturn(true);

        $this->prepareValueFormOptions($value)->shouldReturn([
            'label' => 'Some label',
            'required' => true,
            'auto_initialize' => false,
            'label_attr'      => ['truncate' => true],
            'decimals_allowed' => true
        ]);
    }

    function it_prepares_value_form_constraints($guesser, $attribute, $value)
    {
        $guesser->supportAttribute($attribute)->willReturn(true);
        $guesser->guessConstraints($attribute)->willReturn([]);

        $this->prepareValueFormConstraints($value)->shouldReturn([
            'constraints' => []
        ]);
    }

    function it_prepares_default_value_form_constraints($guesser, $attribute, $value)
    {
        $guesser->supportAttribute($attribute)->willReturn(false);
        $this->prepareValueFormConstraints($value)->shouldReturn([]);
    }

    function it_prepares_value_form_data($value)
    {
        $value->getData()->willReturn('data');
        $this->prepareValueFormData($value)->shouldReturn('data');
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('pim_catalog_number');
    }
}
