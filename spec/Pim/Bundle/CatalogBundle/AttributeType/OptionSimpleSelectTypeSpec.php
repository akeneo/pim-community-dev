<?php

namespace spec\Pim\Bundle\CatalogBundle\AttributeType;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\AttributeType\AbstractAttributeType;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Validator\AttributeConstraintGuesser;
use Prophecy\Argument;
use Symfony\Component\Form\FormFactory;

class OptionSimpleSelectTypeSpec extends ObjectBehavior
{
    function let(AttributeConstraintGuesser $guesser, ProductValueInterface $value, AttributeInterface $attribute)
    {
        $value->getAttribute()->willReturn($attribute);

        $this->beConstructedWith(AbstractAttributeType::BACKEND_TYPE_OPTION, 'pim_ajax_entity', $guesser);
    }

    function it_builds_attribute_form_types(FormFactory $factory, $attribute)
    {
        $attribute->getId()->willReturn(42);
        $attribute->getProperties()->willReturn([]);
        $attribute->setProperty(Argument::any(), Argument::any())->shouldBeCalled();

        $this->buildAttributeFormTypes($factory, $attribute)->shouldHaveCount(6);
    }

    function it_prepares_value_form_name($value, $attribute)
    {
        $attribute->getBackendType()->willReturn(AbstractAttributeType::BACKEND_TYPE_OPTION);
        $this->prepareValueFormName($value)->shouldReturn(AbstractAttributeType::BACKEND_TYPE_OPTION);
    }

    function it_prepares_value_form_alias($value)
    {
        $this->getFormType()->shouldReturn('pim_ajax_entity');
    }

    function it_prepares_value_form_options($value, $attribute)
    {
        $attribute->getLabel()->willReturn('A label');
        $attribute->isRequired()->willReturn(true);
        $attribute->getId()->willReturn(42);
        $attribute->getMinimumInputLength()->willReturn(10);

        $this->prepareValueFormOptions($value)->shouldHaveCount(7);
        $this->prepareValueFormOptions($value)->shouldReturn([
            'label'                => 'A label',
            'required'             => false,
            'auto_initialize'      => false,
            'label_attr'           => ['truncate' => true],
            'class'                => 'PimCatalogBundle:AttributeOption',
            'collection_id'        => 42,
            'minimum_input_length' => 10
        ]);
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('pim_catalog_simpleselect');
    }
}
